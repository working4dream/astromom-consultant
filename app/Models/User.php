<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AwsS3Trait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles,softDeletes,AwsS3Trait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'timezone',
        'email_verified_at',
        'mobile_code',
        'mobile_number',
        'password',
        'otp',
        'about_me',
        'gender',
        'professional_title',
        'social_links',
        'slug',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'postal_code',
        'zego_user_id',
        'device_token',
        'last_logged_in_at',
        'profile_picture',
        'consultancy_area',
        'hourly_rate',
        'hourly_old_rate',
        'description',
        'expertise',
        'philosophy',
        'language',
        'response_time',
        'start_time',
        'end_time',
        'experience',
        'status',
        'image',
        'dob',
        'device_name',
        'notes',
        'cut_out_image',
        'keywords',
        'is_approved',
        'approved_id',
        'reject_reason',
        'is_online',
        'is_top_expert',
        'referral_code',
        'free_chat_used',
        'golden_code_used',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean',
        'is_top_expert' => 'boolean',
    ];

    protected $appends = ['full_name']; // Automatically include full_name in API responses

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getProfilePictureAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }

    public function getCutOutImageAttribute($value)
    {
        return $value ? $this->generateSignedUrl($value) : null;
    }

    // public function getLanguageAttribute($value)
    // {
    //     return implode(', ', explode(',', $value));
    // }

    // public function getProfessionalTitleAttribute($value)
    // {
    //     return implode(', ', explode(',', $value));
    // }

    // public function getKeywordsAttribute($value)
    // {
    //     return implode(', ', explode(',', $value));
    // }

    // public function getExpertiseAttribute($value)
    // {
    //     return implode(', ', explode(',', $value));
    // }

    public function questionDiscussions()
    {
        return $this->hasMany(QuestionDiscussion::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function appointments()
    {
        return $this->hasmany(Appointment::class, 'astrologer_id');
    }
    public function customerAppointments()
    {
        return $this->hasmany(Appointment::class, 'customer_id');
    }

    public function refundRequest()
    {
        return $this->hasmany(RefundRequest::class, 'customer_id');
    }
    public function ratings()
    {
        return $this->hasMany(AstrologerRating::class, 'astrologer_id');
    }
    public function astrologerSchedule()
    {
        return $this->hasMany(AstrologerSchedule::class, 'astrologer_id');
    }
    public function bookNowPrices()
    {
        return $this->hasMany(AstrologerBookNowPrice::class,'astrologer_id');
    }
    public function bankDetails()
    {
        return $this->hasOne(AstrologerBankDetails::class);
    }
    public function approvedEarnings()
    {
        return $this->hasMany(AstrologerEarning::class, 'astrologer_id')->where('status', 1);
    }
    
    public function approvedWithdrawals()
    {
        return $this->hasMany(AstrologerWithdrawal::class, 'astrologer_id')->where('status', 28);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
