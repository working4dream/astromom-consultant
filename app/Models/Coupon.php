<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory,softDeletes;

    protected $fillable=[
        'uuid',
        'name',
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'start_date',
        'expiry_date',
        'active',
        'creator_id',
        'used_counts',
        'used_type'
    ];
}
