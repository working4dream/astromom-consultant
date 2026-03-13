<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getAllPrices()
    {
        return Setting::whereIn('name', [
            'video_30_min_price',
            'video_30_max_price',
            'video_60_min_price',
            'video_60_max_price',
            'voice_30_min_price',
            'voice_30_max_price',
            'voice_60_min_price',
            'voice_60_max_price',
            'chat_min_price',
            'chat_max_price',
            'voice_min_price',
            'voice_max_price',
            'video_min_price',
            'video_max_price',
            'name_correction_consultation',
            'name_correction_with_remdies',
            'exclusive_numerology_full_consultation',
        ])->pluck('data', 'name')->map(function ($value) {
            return number_format((float)$value, 2, '.', '');
        });        
    }

    protected function languageShort($language)
    {
        $languages = explode(',', $language);
        $shortLanguages = array_map(function($lang) {
            return Str::ucfirst(Str::limit(trim($lang), 2, ''));
        }, $languages);
        $languageShort = implode(', ', $shortLanguages);
        return $languageShort;
    }

    public function sendNotificationForAdmin(
        string $title, 
        string $body, 
        array $deviceTokens) 
    {
        $path = base_path('storage/firebase'.'/'.env('FIREBASE_LIVE_FILE'));
        $factory = (new Factory)->withServiceAccount($path);
        $messaging = $factory->createMessaging();

        if (!is_array($deviceTokens)) {
            $deviceTokens = [$deviceTokens];
        }

        $notification = Notification::create($title, $body);

        $androidConfig = AndroidConfig::fromArray([
            'notification' => [
                'sound' => 'alert_sound',
                'channel_id' => 'default_channel',
            ]
        ]);

        $apnsConfig = ApnsConfig::fromArray([
            'payload' => [
                'aps' => [
                    'sound' => 'default',
                ]
            ]
        ]);

        $messages = [];

        foreach ($deviceTokens as $token) {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withAndroidConfig($androidConfig)
                ->withApnsConfig($apnsConfig);

                $messages[] = $message;
        }

        $response = $messaging->sendAll($messages);
        return response()->json(['message' => 'Notification sent successfully']);
    }

    public function createFirebaseDatabase()
    {
        $path = base_path('storage/firebase'.'/'.env('FIREBASE_LIVE_FILE'));
        $factory = (new Factory)->withServiceAccount($path)->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        return $database;
    }

    public function getCities()
    {
        $cities = \DB::table('cities')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'states.country_id', '=', 'countries.id')
            ->select('cities.*')
            ->orderBy('cities.name', 'asc')
            ->get();

        return $cities;
    }

}
