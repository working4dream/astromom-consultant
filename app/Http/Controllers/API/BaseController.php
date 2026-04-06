<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use Kreait\Firebase\Factory;
use App\Http\Controllers\Controller;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message,  $paginate = null)
    {
    	$response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        if ($paginate) {
            $response['pagination'] = [
                'current_page' => $paginate->currentPage(),
                'last_page'    => $paginate->lastPage(),
                'per_page'     => $paginate->perPage(),
                'total'        => $paginate->total(),
            ];
        }
 
        return response()->json($response, 200);
    }
 
    /**
     * Return error JSON. Use 404 only when the requested resource does not exist.
     * Default is 400 (bad request); pass explicit codes for validation (422), not found (404), etc.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 400)
    {
    	$response = [
            'success' => false,
            'message' => $error,
        ];
 
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
 
        return response()->json($response, $code);
    }

    public function generateUniqueOrderId()
    {
        do {
            $orderId = mt_rand(1000000000, 9999999999);
        } while (Order::where('order_id', $orderId)->exists());

        return $orderId;
    }

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

        if (!is_array($deviceTokens) && $deviceTokens) {
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

    public function createFirebaseDatabase()
    {
        $path = base_path('storage/firebase'.'/'.env('FIREBASE_LIVE_FILE'));
        $factory = (new Factory)->withServiceAccount($path)->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        return $database;
    }

}
