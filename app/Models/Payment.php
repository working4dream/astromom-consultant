<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable=[
        'order_id',
        'payment_method',
        'transaction_id',
        'payment_status',
        'payment_date',
        'amount',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function status(){
        return $this->belongsTo(Status::class,'payment_status');
    }
    public function payment_methods()
    {
        return $this->belongsTo(PaymentMethod::class,'payment_method');
    }
}
