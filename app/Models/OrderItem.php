<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable=[
        'order_id',
        'user_id',
        'quantity',
        'amount',
        'discount',
        'total_amount',
    ];
}