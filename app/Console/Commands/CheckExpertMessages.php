<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Message;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class CheckExpertMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expert-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if experts have sent a message within the last 30 seconds and trigger alert if not.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointments = Appointment::where('date', now()->toDateString())
            ->where('connect_type', 'chat')
            ->where('booking_status', 15)
            ->where('end_time', '>', now()->format('H:i'))
            ->get();

        foreach ($appointments as $appointment) {
            $astrologer = User::find($appointment->astrologer_id);
            if (!$astrologer || !$astrologer->zego_user_id) {
                continue;
            }
            $startTime = Carbon::parse($appointment->start_time);
            $endTime = Carbon::parse($appointment->end_time);
            $messageExists = Message::where('sender_id', $astrologer->zego_user_id)
                            ->whereBetween('created_at', [$startTime, $endTime])
                            ->exists();
            $now = Carbon::now();
        
            if (!$messageExists && $now->diffInSeconds($startTime, false) <= -30) {
                $deviceTokens = json_decode($astrologer->device_token);
                if (!is_array($deviceTokens) && $deviceTokens) {
                    $deviceTokens = [$deviceTokens];
                }
                if ($deviceTokens) {
                    $path = base_path('storage/firebase/' . env('FIREBASE_LIVE_FILE'));
                    $factory = (new Factory)->withServiceAccount($path);
                    $messaging = $factory->createMessaging();
                    $title = 'Chat Reminder';
                    $body = 'You have an ongoing chat appointment. Please start the conversation.';
                    $extraData = [
                        'title' => $title,
                        'body' => $body,
                        'type' => 'alert',
                    ];
                
                    $notification = Notification::create($title, $body);
                
                    $androidConfig = AndroidConfig::fromArray([
                        'notification' => [
                            'sound' => 'alert_sound',
                            'channel_id' => 'reminder_channel',
                        ],
                    ]);
                
                    $apnsConfig = ApnsConfig::fromArray([
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
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
                
                    if (!empty($messages)) {
                        $response = $messaging->sendAll($messages);
                    }
                }
            }
        }
    }
}
