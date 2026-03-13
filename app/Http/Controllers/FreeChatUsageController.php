<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Message;
use Illuminate\Http\Request;

class FreeChatUsageController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::query();
        $request->filled('order_no') ? $orders->where('order_id', 'LIKE', "%{$request->order_no}%") : null;

        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            $orders->where(function ($orderQuery) use ($fullName) {
                $orderQuery->whereHas('customer', function ($query) use ($fullName) {
                    if (str_contains($fullName, ' ')) {
                        $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$fullName}%"])
                              ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
                    } else {
                        $query->where(function ($q) use ($fullName) {
                            $q->where('first_name', 'LIKE', "%{$fullName}%")
                              ->orWhere('last_name', 'LIKE', "%{$fullName}%");
                        });
                    }
                })
                ->orWhereHas('astrologer', function ($query) use ($fullName) {
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
        $orders = $orders->where('payment_id', 'freeChat')->orderByDesc('created_at')->paginate(request('items') ?? 20)->withQueryString();
        return view('freeChatUsage.index', compact('orders'));
    
    }
    public function show($id)
    {
        $order = Order::where('id',$id)->first();
        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }
        $astrologerZegoId = $order->astrologer?->zego_user_id;
        $customerZegoId = $order->customer?->zego_user_id;

        
        $messages = Message::where(function ($query) use ($astrologerZegoId, $customerZegoId) {
            $query->where('sender_id', $astrologerZegoId)
            ->where('receiver_id', $customerZegoId);
        })->orWhere(function ($query) use ($astrologerZegoId, $customerZegoId) {
            $query->where('sender_id', $customerZegoId)
            ->where('receiver_id', $astrologerZegoId);
        })->orderBy('created_at', 'asc')->get();

        $groupedMessages = collect($messages)->groupBy(function ($message) {
            return \Carbon\Carbon::parse($message->created_at)->toDateString(); // "2025-06-04"
        });

        $userZegoIds = $messages->pluck('sender_id')->merge($messages->pluck('receiver_id'))->unique();

        $users = User::whereIn('zego_user_id', $userZegoIds)->get()->keyBy('zego_user_id');
        return view('freeChatUsage.show', compact('order', 'groupedMessages','users', 'astrologerZegoId', 'customerZegoId'));
    }
}
