<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UuidTrait;

class Notification extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable=[
        'uuid',
        'user_id',
        'title',
        'subtitle',
        'type',
        'badge_title',
        'image',
        'is_seen',
        'link'
    ];
}
