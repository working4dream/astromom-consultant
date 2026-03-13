<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Status;
use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Models\AstrologerEarning;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AstrologerWalletHistory;

class OrderController extends Controller
{
    public function index(Request $request) 
    {
        $statuses = Status::where('type','order_status')->get();
        $orders = Order::query();
        if($request->filled('order_type')){
            $order_types =request()->order_type;
            $orders->where(function($q) use($order_types) { 
                foreach($order_types as $type){
                    $q->orWhere('typeable_type', 'LIKE', "%{$type}%");
                }
            });
        }
        $request->filled('order_no') ? $orders->where('order_id', 'LIKE', "%{$request->order_no}%") : null;

        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            $orders->whereHas('customer', function ($query) use ($fullName) {
                if (str_contains($fullName, ' ')) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$fullName}%"])
                            ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
                } else {
                    $query->where(function ($q) use ($fullName) {
                        $q->where('first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('last_name', 'LIKE', "%{$fullName}%");
                    });
                }
            });
        }        

        if($request->filled('date')){
            $date = explode('to',$request->date);
            if(count($date) === 1){
                $start_date=$date[0];
                $orders->whereDate('created_at', $start_date);
            }
            else {
                $start_date=$date[0];
                $to_date=$date[1];
                $orders->whereDate('created_at', '>=', $start_date);
                $orders->whereDate('created_at', '<=', $to_date);
            }
        }

        $request->filled('order_status') ? $orders->where('order_status', 'LIKE', "%{$request->order_status}%") : null;
        $customers = User::role('customer')->orderByDesc('id')->get();
        $orders = $orders->with('typeable','customer','coupon')->orderByDesc('created_at')->paginate(request('items') ?? 20)->withQueryString();
        return view('orders.index', compact('orders', 'statuses', 'customers'));
    }
    public function show($id){
        $order = Order::where('id',$id)->first();
        return view('orders.show', compact('order'));
    }
    public function export(){
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }
    public function createAppointment(Request $request)
    {
        $durationSeconds = $request->duration * 60;
        $bookingId = mt_rand(1000000000, 9999999999);
        $timePeriod = $request->start_time . '-' . $request->end_time;

        while (Appointment::where('booking_id', $bookingId)->exists()) {
            $bookingId = mt_rand(1000000000, 9999999999);
        }
        $appointment = Appointment::create([
            'customer_id' => $request->customer_id,
            'astrologer_id' => $request->astrologer_id,
            'booking_id' => $bookingId,
            'date' => $request->date,
            'connect_type' => $request->connect_type,
            'duration' => $request->duration,
            'duration_second' => $durationSeconds,
            'time_period' => $timePeriod,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'price' => $request->price,
            'gst' => $request->gst,
            'total_price' => $request->total_price,
            'payment_id' => $request->payment_id ?? 'freeAppointment',
            'booking_status' => 15,
        ]);

        do {
            $orderId = mt_rand(1000000000, 9999999999);
        } while (Order::where('order_id', $orderId)->exists());

        $order = Order::create([
            'order_id' => $orderId,
            'customer_id' => $request->customer_id,
            'astrologer_id' => $request->astrologer_id,
            'typeable_id' =>  $appointment->id,
            'typeable_type' => Appointment::class,
            'price' => $request->price,
            'gst' => $request->gst,
            'discount' => 0.00,
            'total_price' => $request->total_price,
            'payment_id' => $request->payment_id ?? 'freeAppoinment',
            'order_status' => 7,
        ]);
        $walletHistory = AstrologerWalletHistory::create([
            'astrologer_id' => $request->astrologer_id,
            'type' => 1,
            'message' => 'credited to your account for the appointment #' . $appointment->booking_id,
            'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
        ]);
        $earning = AstrologerEarning::create([
            'astrologer_id' => $request->astrologer_id,
            'appointment_id' => $appointment->id,
            'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
        ]);
        // Send Notification
        // For Customer
        $deviceTokensCustomer = json_decode($appointment->customer->device_token);
        $titleCustomer = "Appointment Confirmed";
        $messageCustomer = "Your appointment with ". $appointment->astrologer->full_name ." is confirmed for ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $appointment->start_time .". Be ready for your session.";
        if (!empty($deviceTokensCustomer)) {
            $this->sendNotificationForAdmin($titleCustomer, $messageCustomer, $deviceTokensCustomer);
        }
        Notification::create([
            'user_id' => $appointment->customer->id,
            'title' => $titleCustomer,
            'subtitle' => $messageCustomer,
            'type' => 'general',
        ]);
        // For Expert
        $deviceTokens = json_decode($appointment->astrologer->device_token);
        $title = "New Appointment Scheduled";
        $message = "You have a new appointment with ". $appointment->customer->full_name ." on ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $appointment->start_time .". Please be prepared.";
        if (!empty($deviceTokens)) {
            $this->sendNotificationForAdmin($title, $message, $deviceTokens);
        }
        Notification::create([
            'user_id' => $appointment->astrologer->id,
            'title' => $title,
            'subtitle' => $message,
            'type' => 'general',
        ]);
        // $ref = $this->createFirebaseDatabase()->getReference('orderCount/'.$appointment->astrologer->id);
        // $existingData = $ref->getValue();
        // if ($existingData) {
        //     $newCount = isset($existingData['count']) ? $existingData['count'] + 1 : 1;
        //     $ref->update(['count' => $newCount]);
        // } else {
        //     $ref->set([
        //         'id' => $appointment->astrologer->id,
        //         'zego_user_id' => $appointment->astrologer->zego_user_id,
        //         'count' => 1
        //     ]);
        // }
        return redirect()->back()->with('success', 'Appointment Created Successfully');
    }
}