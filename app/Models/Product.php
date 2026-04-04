<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'duration',
        'duration_in_min',
        'description',
        'price',
        'is_gst',
        'gst_type',
        'gst_amount',
        'total_price',
        'status',
    ];
}
