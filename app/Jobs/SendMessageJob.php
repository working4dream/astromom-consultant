<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Status;
use Carbon\Carbon;
use App\Services\NotificationService;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $requestData, $sender;
    /**
     * Create a new job instance.
     */
    public function __construct(array $requestData, $sender)
    {
        $this->requestData = $requestData;
        $this->sender = $sender;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $request = (object) $this->requestData;

        $senderId = (int) $this->sender->zego_user_id;
        $receiverId = (int) $request->receiver_id;

        if ($request->is_chat_screen === 0) {
            $receiverUser = User::where('zego_user_id', $receiverId)->first();
            $appointment = Appointment::find($request->appointment_id ?? null);

            if ($receiverUser && $appointment) {
                $deviceTokens = json_decode($receiverUser->device_token);
                $title = "New message arrived from " . $this->sender->full_name;
                $body = "You have an active chat session. Tap to reply.";
                $status = Status::find($appointment->booking_status);

                $extraData = [
                    'title' => $title,
                    'body' => $body,
                    'type' => 'chat_message',
                    'id' => $appointment->id,
                    'name' => $this->sender->first_name . ' ' . $this->sender->last_name,
                    'profile_picture' => $this->sender->profile_picture,
                    'zego_user_id' => $senderId,
                    'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                    'time' => $appointment->time_period,
                    'duration' => $appointment->duration,
                    'booking_status_name' => $status?->name ?? ''
                ];

                if (!empty($deviceTokens)) {
                    $notificationService->sendNotification($title, $body, $deviceTokens, $extraData); 
                }
            }
        }
    }
}
