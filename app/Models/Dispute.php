<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AwsS3Trait;
use Illuminate\Database\Eloquent\SoftDeletes;


class Dispute extends Model
{
    use HasFactory, AwsS3Trait,SoftDeletes;

    protected $fillable = [
        "customer_id",
        "ticket_id",
        "reason",
        "other_reason",
        "booking_id",
        "appointment_date",
        "description",
        "file",
        "status",
    ];

    public function getFileAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
    public function appointment(){
        return $this->belongsTo(Appointment::class,'booking_id');
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function disputeStatus()
    {
        return $this->belongsTo(Status::class, 'status');
    }
    public function discussions()
    {
        return $this->hasMany(DisputeDiscussion::class, 'dispute_id');
    }
}
