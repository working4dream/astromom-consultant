<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AstrologerBankDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'beneficiary_name',
        'account_number',
        'ifsc_code',
        'name',
        'pan_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
