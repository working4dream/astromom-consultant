<?php

namespace App\Jobs;

use App\Models\Notification as NotificationModel;
use App\Models\User;
use App\Traits\PushNotificationTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendUserNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PushNotificationTrait;

    protected $user;
    protected $title;
    protected $message;
    protected $buttonText;
    protected $link;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $title, $message, $buttonText, $link)
    {
        $this->user = $user;
        $this->title = $title;
        $this->message = $message;
        $this->buttonText = $buttonText;
        $this->link = $link;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = Carbon::now();
        if ($this->user->device_token) {
            $deviceTokens = is_string($this->user->device_token) ? 
                json_decode($this->user->device_token, true) : [$this->user->device_token];

            if (is_array($deviceTokens) && !empty($deviceTokens)) {
                $path = base_path('storage/firebase'.'/'.env('FIREBASE_LIVE_FILE'));
                $factory = (new Factory)->withServiceAccount($path);
                $messaging = $factory->createMessaging();

                if (!is_array($deviceTokens)) {
                    $deviceTokens = [$deviceTokens];
                }

                $notification = Notification::create($this->title, $this->message);

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

                $extraData = [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'button_text' => $this->buttonText,
                    'action' => 'click_notification',
                    'url' => $this->link,
                    'type' => 'click_button',
                    'link_type' => 'external',
                ];

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
                
                NotificationModel::create([
                    'user_id' => $this->user->id,
                    'title' => $this->title,
                    'subtitle' => $this->message,
                    'type' => 'external',
                    'link' => $this->link,
                ]);
            }
        }
        $endTime = Carbon::now(); // End time
        $duration = $endTime->diffInSeconds($startTime);
    
        Log::info("Notification sent to user ID {$this->user->id} in {$duration} seconds.");
    }
}
