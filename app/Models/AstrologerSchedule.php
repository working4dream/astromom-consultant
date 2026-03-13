<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'future_days',
        'duration_minutes',
        'schedule',
        'morning_price',
        'afternoon_price',
        'evening_price',
        'video_call_price_30min',
        'video_call_price_60min',
        'audio_call_price_30min',
        'audio_call_price_60min',
        'not_available_days',
        'is_availability',
    ];

    protected $hidden = ['price', 'duration_minutes', 'evening_price', 'afternoon_price', 'morning_price', 'created_at', 'updated_at'];

    public function astrologer()
    {
        return $this->belongsTo(User::class,'astrologer_id');
    }
}
