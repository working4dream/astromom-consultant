<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\AwsS3Trait;

class Banner extends Model
{
    use HasFactory, AwsS3Trait;
    protected $fillable = [
        'customer_banner',
        'expert_banner',
        'link_type',
        'link',
        'type',
        'is_active',
        'start_date',
        'end_date',
        'date_range',
    ];

    public function getCustomerBannerAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }

    public function getExpertBannerAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }

    public function bannerClick()
    {
        return $this->hasMany(BannerClick::class);
    }
}
