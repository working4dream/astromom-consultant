<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_id',
        'appointment_id',
        'call_time',
        'caller_id',
        'receiver_id',
        'type',
        'status',
        'date',
        'started_at',
        'ended_at',
        'duration',
        'session_start_time',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
    public function astrologer()
    {
        return $this->belongsTo(User::class,'caller_id');
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class,'appointment_id');
    }
}
