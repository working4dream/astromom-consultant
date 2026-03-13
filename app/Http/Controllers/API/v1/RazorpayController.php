<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use Razorpay\Api\Api;

class RazorpayController extends BaseController
{
    public function createOrder(Request $request)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        
        $order = $api->order->create([
            'receipt' => 'receipt_' . uniqid(),
            'amount' => intval($request->amount * 100),
            'currency' => 'INR',
            'payment_capture' => 1
        ]);
        $data = [
            'order_id' => $order->id,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'key' => config('services.razorpay.key'),
        ];

        return $this->sendResponse($data, 'Order Confirmed!');
    }
}
