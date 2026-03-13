<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Refund;
use App\Models\Status;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index() 
    {
        $payments = Payment::with('order','status')->paginate(20)->withQueryString();
        return view('payments.index', compact('payments'));
    }

    public function refunds(){
        $refunds = Refund::with('order','user','status')->paginate(20)->withQueryString();
        return view('payments.refunds', compact('refunds'));
    }
    
    public function paymentSuccess(){
        return view('payments.success');
    }
}
