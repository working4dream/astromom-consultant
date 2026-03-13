<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'amount',
        'status',
        'reject_reason',
        'comment',
    ];

    public function astrologer()
    {
        return $this->belongsTo(User::class,'astrologer_id');
    }
}
