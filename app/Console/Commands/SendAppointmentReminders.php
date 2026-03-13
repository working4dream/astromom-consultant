<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Status;
use App\Models\Appointment;
use Kreait\Firebase\Factory;
use Illuminate\Console\Command;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointment:send-reminders';

    protected $description = 'Send reminders before appointment start time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendReminders(10, 11, 'Your Appointment Starts in 10 Minutes!', 'Upcoming Appointment in 10 Minutes!');
        $this->sendReminders(2, 3, 'Your Appointment is About to Start!', 'Appointment Starting Now!');

        $this->info('Appointment reminders sent successfully!');
    }

    private function sendReminders($startMinute, $endMinute, $customerTitle, $astrologerTitle)
    {
        $today = Carbon::now()->toDateString();
        $appointments = Appointment::whereDate('date', $today)
            ->whereBetween('start_time', [
                Carbon::now()->addMinutes($startMinute)->format('H:i:s'),
                Carbon::now()->addMinutes($endMinute)->format('H:i:s')
            ])
            ->where('booking_status', 15)
            ->get();
        foreach ($appointments as $appointment) {
            $isOngoing = Appointment::where('astrologer_id', $appointment->astrologer_id)
                ->where('customer_id', $appointment->customer_id)
                ->where('date', now()->toDateString())
                ->where('booking_status', 15)
                ->where('start_time', '<=', now()->format('H:i'))
                ->where('end_time', '>=', now()->format('H:i'))
                ->exists();
            if ($isOngoing) {
                break;
            }
            $this->notifyUser($appointment, 'customer', $customerTitle, $startMinute);
            $this->notifyUser($appointment, 'astrologer', $astrologerTitle, $startMinute);
        }
    }

    private function notifyUser($appointment, $userType, $title, $startMinute)
    {
        $user = $userType === 'customer' ? $appointment->customer : $appointment->astrologer;

        if ($user && $user->device_token) {
            $deviceTokens = json_decode($user->device_token);

            if (!is_array($deviceTokens)) {
                $deviceTokens = [$deviceTokens];
            }

            $path = base_path('storage/firebase/' . env('FIREBASE_LIVE_FILE'));
            $factory = (new Factory)->withServiceAccount($path);
            $messaging = $factory->createMessaging();

            if ($userType === 'customer') {
                $body = $startMinute === 10
                    ? 'Your appointment with '. $appointment->astrologer->full_name.' is in 10 minutes. Get ready!'
                    : 'Your appointment with '. $appointment->astrologer->full_name.' is starting soon. Please be ready!';
            } else {
                $body = $startMinute === 10
                    ? 'Your appointment with '. $appointment->customer->full_name.' is in 10 minutes. Get ready!'
                    : 'Your appointment with '. $appointment->customer->full_name.' is starting soon. Please be ready!';
            }
            $notification = Notification::create($title, $body);

            $androidConfig = AndroidConfig::fromArray([
                'notification' => [
                    'sound' => 'alert_sound',
                    'channel_id' => 'reminder_channel'
                ]
            ]);

            $apnsConfig = ApnsConfig::fromArray([
                'payload' => [
                    'aps' => [
                        'sound' => 'default'
                    ]
                ]
            ]);

            $extraData = [
                'title' => $title,
                'body' => $body,
                'type' => 'reminder',
                'id' => $appointment->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'profile_picture' => $user->profile_picture,
                'zego_user_id' => $user->zego_user_id,
                'connect_type' => $appointment->connect_type,
                'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                'time' => $appointment->time_period,
                'duration' => $appointment->duration,
                'booking_status_name' => Status::find($appointment->booking_status)->name
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

            $messaging->sendAll($messages);
        }
    }
}
