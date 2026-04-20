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
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');

        if (empty($key) || empty($secret)) {
            return $this->sendError('Razorpay credentials not configured.', [], 500);
        }

        $api = new Api($key, $secret);
        
        $order = $api->order->create([
            'receipt' => 'receipt_' . uniqid(),
            'amount' => intval($request->amount * 100),
            'currency' => $request->currency ?? config('app.currency_code', 'INR'),
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
