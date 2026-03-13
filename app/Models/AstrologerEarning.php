<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'appointment_id',
        'amount',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function astrologer()
    {
        return $this->belongsTo(User::class);
    }
    public function earnings()
    {
        return $this->hasMany(AstrologerEarning::class, 'astrologer_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(AstrologerWithdrawal::class, 'astrologer_id');
    }
}
