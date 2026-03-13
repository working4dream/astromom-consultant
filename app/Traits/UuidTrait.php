<?php

namespace App\Traits;
use Illuminate\Support\Str;

trait UuidTrait
{
   
    /**
     * Boot the UuidTrait for the model.
     *
     * @return void
     */
    protected static function bootUuidTrait()
    {
        static::creating(function ($model) {
            // Check if 'uuid' column exists and isn't already set
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Use 'uuid' as the route key name for model binding.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
