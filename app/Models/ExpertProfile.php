<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AwsS3Trait;

class ExpertProfile extends Model
{
    use HasFactory, AwsS3Trait;
    protected $fillable=[
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'profile_picture',
    ];

    public function getProfilePictureAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }
}
