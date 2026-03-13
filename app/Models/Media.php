<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AwsS3Trait;

class Media extends Model
{
    use HasFactory, AwsS3Trait;

    protected $fillable = [
        'name',
        'user_id',
        'storage',
        'type',
        'extension',
        'size',
        'path',
        'video_path'
    ];

    public function getVideoPathAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
}
