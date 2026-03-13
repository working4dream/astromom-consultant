<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Refund extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable=[
        'order_id',
        'user_id',
        'order_id',
        'reason',
        'refund_status',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function status(){
        return $this->belongsTo(Status::class,'refund_status');
    }
}
