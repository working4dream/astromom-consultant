<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerWalletHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'type',
        'message',
        'amount',
    ];
}
