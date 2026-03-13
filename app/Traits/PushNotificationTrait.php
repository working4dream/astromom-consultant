<?php

namespace App\Traits;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

trait PushNotificationTrait
{
    public function sendNotificationForAdmin(string $title, string $body, array $deviceTokens) 
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
                'channel_id' => 'default_channel'
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
}
