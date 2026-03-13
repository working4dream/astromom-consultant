<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBillingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'postal_code',
    ];
    
    public function country(){
        return $this->belongsTo(Country::class);
    
    }
    public function state(){
        return $this->belongsTo(State::class);
    
    }
    public function city(){
        return $this->belongsTo(City::class);
    
    }
}
