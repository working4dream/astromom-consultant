<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisputeDiscussion extends Model
{
    use HasFactory,softDeletes;

    protected $fillable = ['dispute_id','user_id','message'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function dispute()
    {
        return $this->belongsTo(Dispute::class);
    }
}
