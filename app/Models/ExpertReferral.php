<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpertReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'astrologer_id',
        'referral_code',
        'share_count',
        'download_count',
    ];
}
