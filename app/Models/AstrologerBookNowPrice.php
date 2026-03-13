<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerBookNowPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'chat_price',
        'voice_price',
        'video_price',
    ];

    public function astrologer()
    {
        return $this->belongsTo(user::class);
    }
}
