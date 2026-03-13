<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CallLog;
use App\Models\Appointment;
use Illuminate\Console\Command;

class CompleteOngoingChatAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:complete-ongoing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete chat appointments that are currently ongoing and should be marked as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $appointments = Appointment::where('connect_type', 'chat')
            ->where(function ($query) {
                $query->where('date', '<', now()->toDateString())
                    ->orWhere(function ($query) {
                        $query->where('date', now()->toDateString())
                                ->where('end_time', '<', now()->format('H:i'));
                    });
            })->get();
        
        foreach ($appointments as $appointment) {
            $this->info("Processing Appointment ID: {$appointment->id}");
            $callLog = CallLog::where('appointment_id', $appointment->id)
            ->whereNull('ended_at')
            ->first();
            if ($callLog) {
                $startTime = Carbon::parse($callLog->started_at);
                $endTime = Carbon::parse($appointment->end_time);
                $duration = $startTime->diffInSeconds($endTime);
                $minutes = floor($duration / 60);
                $seconds = $duration % 60;
                $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
                $callLog->ended_at = $appointment->end_time;
                $callLog->call_time = $formattedTime;
                $callLog->status = 25;
                $callLog->save();
                $this->info("Updated call log end_time for appointment ID {$appointment->id}");
            } else {
                $this->warn("Call log not found for appointment ID {$appointment->id}");
            }
            $appointment->booking_status = 17;
            $appointment->save();
            $this->info("Updated booking_status to 17 for appointment ID {$appointment->id}");
        }

        $this->info("Completed processing ongoing appointments.");

    }
}
