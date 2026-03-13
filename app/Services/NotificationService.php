<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;

class NotificationService
{
    public function sendNotification(
        string $title, 
        string $body, 
        array $deviceTokens, 
        array $extraData = [], 
        string $channelId = 'default_channel',
        string $customSound = 'default') 
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
                'sound' => $customSound,
                'channel_id' => $channelId
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
                ->withApnsConfig($apnsConfig)
                ->withData($extraData);

                $messages[] = $message;
        }

        $response = $messaging->sendAll($messages);
        return response()->json(['message' => 'Notification sent successfully']);
    }

}