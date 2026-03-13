<?php

namespace App\Http\Controllers\API\v2;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\AstrologerSchedule;
use App\Http\Controllers\API\BaseController;
use App\Models\AstrologerBookNowPrice;

class AppointmentController extends BaseController
{
    public function getAstrologerSchedule(Request $request, $id)
    {
        $duration_minutes = (int)$request->duration_minutes;

        $schedule = AstrologerSchedule::where('astrologer_id', $id)->where('is_availability', 1)->first();
        $chatschedule = AstrologerBookNowPrice::where('astrologer_id', $id)->first();
        if (!$schedule) {
            return $this->sendResponse([], 'Schedule not found.');
        }

        function generateSlots($startTime, $endTime, $durationMinutes, $currentTime = null)
        {
            $slots = [];
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            while ($start->lt($end)) {
                if ($currentTime === null || $start->gte($currentTime)) {
                    $slotStart = $start->format('H:i');
                    $start->addMinutes($durationMinutes);
                    $slotEnd = $start->format('H:i');

                    if ($start->lte($end) && $slotEnd <= $end->format('H:i')) {
                        $slots[] = $slotStart . '-' . $slotEnd;
                    }
                } else {
                    $start->addMinutes($durationMinutes);
                }
            }

            return $slots;
        }

        function getNextSlotStart($currentTime, $durationMinutes)
        {
            $minutes = ceil($currentTime->minute / $durationMinutes) * $durationMinutes;
            return $currentTime->copy()->minute(0)->second(0)->addMinutes($minutes);
        }

        $scheduleData = json_decode($schedule->schedule, true);
        $notAvailableDays = json_decode($schedule->not_available_days, true) ?? [];

        $priceMap = [
            'video' => [
                30 => $schedule->video_call_price_30min,
                60 => $schedule->video_call_price_60min,
            ],
            'voice' => [
                30 => $schedule->audio_call_price_30min,
                60 => $schedule->audio_call_price_60min,
            ],
            'chat' => [
                30 => number_format((float)$chatschedule->chat_price * 30, 2, '.', ''),
                60 => number_format((float)$chatschedule->chat_price * 60, 2, '.', ''),                
            ],
        ];
        $price = $priceMap[$request->connect_type][$duration_minutes] ?? 0;

        $finalSchedule = [];

        for ($i = 0; $i < 8; $i++) {
            $date = Carbon::now()->addDays($i)->toDateString();
            $currentDay = Carbon::parse($date)->format('l');
            $todaySchedule = array_filter($scheduleData, function ($daySchedule) use ($currentDay) {
                return $daySchedule['day'] === $currentDay;
            });

            if (in_array($date, $notAvailableDays) || empty($todaySchedule)) {
                continue;
            }

            $todaySchedule = array_values($todaySchedule)[0];
            $periods = $todaySchedule['time_periods'];

            $isToday = $date === Carbon::now()->toDateString();
            $currentTime = $isToday ? Carbon::now() : null;

            $bookedSlots = Appointment::where('astrologer_id', $id)
                ->where('date', $date)
                ->where('booking_status', 15)
                ->pluck('time_period')
                ->toArray();

            $morningSlots = [];
            $afternoonSlots = [];
            $eveningSlots = [];

            foreach ($periods as $period) {
                $startTime = Carbon::parse($period['start_time']);
                $endTime = Carbon::parse($period['end_time']);

                $effectiveStartTime = $isToday ? max($startTime, getNextSlotStart($currentTime, $duration_minutes)) : $startTime;

                $morningEnd = Carbon::parse('12:00');
                if ($startTime->lt($morningEnd)) {
                    $morningSlots = array_merge(
                        $morningSlots,
                        generateSlots($effectiveStartTime, min($endTime, $morningEnd), $duration_minutes, $currentTime)
                    );
                }

                $afternoonStart = Carbon::parse('12:00');
                $afternoonEnd = Carbon::parse('17:00');
                if ($startTime->lt($afternoonEnd) && $endTime->gt($afternoonStart)) {
                    $afternoonSlots = array_merge(
                        $afternoonSlots,
                        generateSlots(max($effectiveStartTime, $afternoonStart), min($endTime, $afternoonEnd), $duration_minutes, $currentTime)
                    );
                }

                $eveningStart = Carbon::parse('17:00');
                if ($startTime->lt($endTime) && $endTime->gt($eveningStart)) {
                    $eveningSlots = array_merge(
                        $eveningSlots,
                        generateSlots(max($effectiveStartTime, $eveningStart), $endTime, $duration_minutes, $currentTime)
                    );
                }
            }

            $allSlots = array_merge($morningSlots, $afternoonSlots, $eveningSlots);
            $availableSlots = array_values(array_diff($allSlots, $bookedSlots));

            $finalSchedule[] = [
                'date' => $date,
                'day' => $currentDay,
                'available_slot_count' => count($availableSlots),
                'booked_slots' => array_values($bookedSlots),
                'schedule' => $availableSlots,
            ];
        }

        $data = [
            "id" => $schedule->id,
            "future_days" => $schedule->future_days,
            "duration_minutes" => $duration_minutes,
            "price" => $price,
            "schedule" => $finalSchedule
        ];

        return $this->sendResponse($data, 'Schedule retrieved successfully.');

    }
}
