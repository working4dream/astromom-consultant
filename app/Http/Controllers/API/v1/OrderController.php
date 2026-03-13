<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Http\Controllers\API\BaseController;

class OrderController extends BaseController
{
    public function getOrders(Request $request)
    {
        $user = auth('api')->user();
        if ($user->hasRole('customer')) {
            $orders = Order::where('customer_id', $user->id)->orderBy('created_at', 'desc')->paginate($request->per_page);
        } elseif ($user->hasRole('astrologer')) {
            $orders = Order::where('astrologer_id', $user->id)->orderBy('created_at', 'desc')->paginate($request->per_page);
        } else {
            $orders = collect();
        }
        if($orders->isEmpty()){
            return $this->sendResponse([], 'Order not found');
        }
        $data = $orders->groupBy(function ($order) use ($user) {
            $date = $order->created_at;
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) use ($user) {
            return [
                'day' => $date,
                'data' => $group->map(function ($order) use ($user) {
                    $name = null;
                    $heading = null;
                    $bookingId = null;
                    $image = null;
                    $title = null;
                    $connectType = null;
                    $date = null;
                    $time = null;
                    $delivered = null;
                    $isRenewed = false;
                    if ($order->typeable_type === 'App\Models\Appointment') {
                        if ($user->hasRole('astrologer')) {
                            $customer = $order->typeable?->customer()->withTrashed()->first();
                            $name = $customer?->full_name;
                            $bookingId = $order->typeable?->booking_id;
                            $heading = 'Appointment with';
                            $image = $customer?->profile_picture;
                            $title = $customer?->professional_title;
                            $date = Carbon::parse($order->typeable?->date)->format('d-M-Y');
                            $time = $order->typeable?->time_period;
                            $connectType = $order->typeable?->connect_type;
                        } elseif ($user->hasRole('customer')){
                            $astrologer = $order->typeable?->astrologer()->withTrashed()->first();
                            $name = $astrologer?->full_name;
                            $bookingId = $order->typeable?->booking_id;
                            $heading = 'Appointment with';
                            $image = $astrologer?->profile_picture;
                            $title = $astrologer?->professional_title;
                            $date = Carbon::parse($order->typeable?->date)->format('d-M-Y');
                            $time = $order->typeable?->time_period;
                            $connectType = $order->typeable?->connect_type;
                        }
                    }
                    return [
                        'id' => $order->id,
                        'heading' => $heading,
                        'order_id' => $order->order_id,
                        'booking_id' => $bookingId,
                        'name' => $name,
                        'image' => $image,
                        'title' => $title,
                        'total_price' => $order->total_price,
                        'connect_type' => $connectType,
                        'date' => $date,
                        'time' => $time,
                        'delivered_date' => $delivered,
                        'is_renewed' => $isRenewed,
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Orders retrived successfully.', $orders);
    }

    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'order_amount' => 'required|min:0',
            'used_type' => 'required'
        ]);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
    
        $coupon = Coupon::where('code', $request->code)->where('used_type', 'LIKE' , '%'.strtolower($request->used_type).'%')->where('active',1)->first();
    
        if (!$coupon) {
            return $this->sendError('Invalid coupon code.');
        }
    
        $currentDate = now();
        if($coupon->start_date && $coupon->expiry_date) {
            if ($currentDate < $coupon->start_date || $currentDate > $coupon->expiry_date) {
                return $this->sendError('This coupon has expired or is not yet active.');
            }
        }
        if ($request->order_amount < $coupon->min_order_amount) {
            return $this->sendError('Minimum order amount required is ' . $coupon->min_order_amount);
        }
    
        $discount = 0;
        if ($coupon->discount_type == 'percentage') {
            $discount = ($request->order_amount * $coupon->discount_value) / 100;
            if ($coupon->max_discount !== "0.00" && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } elseif ($coupon->discount_type == 'fixed') {
            $discount = $coupon->discount_value;
        }
        // used_count validation
        if ((int)$coupon->used_counts === 0) {
            return $this->sendError('This coupon has reached its usage limit.');
        }
        elseif((int)$coupon->used_counts > 0){
            $coupon->decrement('used_counts');
        }

        $discountedAmount = $request->order_amount - $discount;
        if ($request->gst_type === 'inclusive') {
            $gst = 0;
        } else {
            $gst = ($discountedAmount * 18) / 100;
        }        
        $finalAmount = $discountedAmount + $gst;

        $data = [
            'id' => $coupon->id,
            'original_amount' => round($request->order_amount, 2),
            'discount' => round($discount, 2),
            'gst' => round($gst, 2),
            'final_amount' => round($finalAmount, 2),
        ];
        
        return $this->sendResponse($data, 'Coupon applied successfully.');
    }

    public function getCouponAvailable(Request $request)
    {
        $isAvailable = Coupon::where('used_type', 'LIKE', '%'.$request->used_type.'%')->exists();
        $data = [
            'isAvailable' => $isAvailable,
        ];
        return $this->sendResponse($data, 'Coupon applied successfully.');
    }

    public function orderCancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'reason' => 'required',
            'comment' => 'required',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
        
        $user = auth('api')->user();
        $existingRequest = RefundRequest::where([
            'order_id' => $request->order_id,
            'customer_id' => $user->id
        ])->exists();
    
        if ($existingRequest) {
            return $this->sendError('You are already sent refund request');
        }

        $refund = RefundRequest::create([
            'order_id' => $request->order_id,
            'customer_id' => $user->id,
            'reason' => $request->reason,
            'comment' => $request->comment,
        ]);

        $status = Status::where('id', 12)->pluck('name', 'id')->first();
        $refund->refund_status_name = $status ?? null;
        return $this->sendResponse($refund, 'Refund request sent successfully.');
    }

    public function getRefundRequests(Request $request)
    {
        $user = auth('api')->user();
        $requests = RefundRequest::where('customer_id', $user->id)->orderBy('created_at', 'desc')->paginate($request->per_page);
        $data = $requests->groupBy(function ($request) {
            $date = $request->created_at;
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($request) {
                    $astrologer = $request->order->typeable->astrologer()->withTrashed()->first();
                    $status = Status::where('id', $request->status)->pluck('name', 'id')->first();
                    return [
                        'id' => $request->id,
                        'order_id' => '#'.$request->order->order_id,
                        'name' => $astrologer->full_name,
                        'profile_picture' => $astrologer->profile_picture,
                        'connect_type' => $request->order->typeable->connect_type,
                        'request_date' => Carbon::parse($request->created_at)->format('d-M-Y'),
                        'refund_amount' => $request->order->total_price,
                        'status' => $status,
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Refund request retrived successfull', $requests);
    }
}
