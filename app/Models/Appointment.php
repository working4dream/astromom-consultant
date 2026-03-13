<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'customer_id',
        'booking_id',
        'date',
        'connect_type',
        'duration',
        'duration_second',
        'time_period',
        'start_time',
        'end_time',
        'price',
        'gst',
        'discount',
        'total_price',
        'booking_status',
        'payment_id',
        'is_waiting',
        'service_type',
        'is_extended_chat',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function astrologer()
    {
        return $this->belongsTo(User::class,'astrologer_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'booking_status');
    }
    public function orders()
    {
        return $this->morphMany(Order::class, 'typeable');
    }
    public function earnings()
    {
        return $this->hasMany(AstrologerEarning::class);
    }
    public function appointmentRatings()
    {
        return $this->hasMany(AppointmentRating::class);
    }
    public function callLog()
    {
        return $this->hasOne(CallLog::class);
    }

}
