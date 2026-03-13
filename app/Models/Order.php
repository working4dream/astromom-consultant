<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable=[
        'order_id',
        'customer_id',
        'coupon_id',
        'astrologer_id',
        'typeable_id',
        'typeable_type',
        'price',
        'gst',
        'discount',
        'total_price',
        'order_status',
        'payment_id',
        'is_renewed',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function astrologer()
    {
        return $this->belongsTo(User::class, 'astrologer_id');
    }
    public function payment()
    {
        return $this->hasOne(Payment::class,'order_id');
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class,'coupon_id');
    }
    public function status(){
        return $this->belongsTo(Status::class,'order_status');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function billing()
    {
        return $this->hasOne(OrderBillingAddress::class,'order_id');
    }

    public function typeable()
    {
        return $this->morphTo();
    }
    public function refundRequest()
    {
        return $this->hasMany(RefundRequest::class);
    }
}
