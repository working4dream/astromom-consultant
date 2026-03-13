<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use Razorpay\Api\Api;
use App\Models\Status;
use App\Models\CallLog;
use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\AstrologerRating;
use App\Models\AppointmentRating;
use App\Models\AstrologerEarning;
use App\Models\AstrologerSchedule;
use Illuminate\Support\Facades\DB;
use App\Models\AstrologerBookNowPrice;
use App\Models\AstrologerWalletHistory;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Log;

class AppointmentController extends BaseController
{
    public function getAstrologerSchedule(Request $request,$id)
    {
        $duration_minutes = (int)$request->duration_minutes;
        $schedule = AstrologerSchedule::where('astrologer_id', $id)->where('is_availability', 1)->first();
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

        $scheduleData = json_decode($schedule->schedule, true);
        $currentDay = Carbon::parse($request->date)->format('l');
        $todaySchedule = array_filter($scheduleData, function ($daySchedule) use ($currentDay) {
            return $daySchedule['day'] === $currentDay;
        });
        $notAvailableDays = json_decode($schedule->not_available_days, true) ?? [];
        if (in_array($request->date, $notAvailableDays) || empty($todaySchedule)) {
            return $this->sendResponse([], 'No schedule available for this day.');
        }

        $todaySchedule = array_values($todaySchedule)[0];
        $periods = $todaySchedule['time_periods'];

        $currentDate = Carbon::now()->toDateString();
        $isToday = $request->date === $currentDate;
        $currentTime = $isToday ? Carbon::now() : null;

        function getNextSlotStart($currentTime, $durationMinutes)
        {
            $minutes = ceil($currentTime->minute / $durationMinutes) * $durationMinutes;
            return $currentTime->copy()->minute(0)->second(0)->addMinutes($minutes);
        }

        $bookedSlots = Appointment::where('astrologer_id', $id)
            ->where('date', $request->date)
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

        $priceMap = [
            'video' => [
                30 => $schedule->video_call_price_30min,
                60 => $schedule->video_call_price_60min,
            ],
            'voice' => [
                30 => $schedule->audio_call_price_30min,
                60 => $schedule->audio_call_price_60min,
            ],
        ];
        
        $price = $priceMap[$request->connect_type][$duration_minutes] ?? 0;

        $data = [
            "id" => $schedule->id,
            "future_days" => $schedule->future_days,
            "duration_minutes" => $duration_minutes,
            "schedule" => [
                "morning" => $morningSlots,
                "afternoon" => $afternoonSlots,
                "evening" => $eveningSlots,
            ],
            "price" => $price,
            "booked_slots" => $bookedSlots
        ];
        return $this->sendResponse($data, 'Schedule retrieved successfully.');
    }

    public function getPriceSettings(Request $request,$id)
    {
        $astrologer = User::role('astrologer')->find($id);
        if (!$astrologer) {
            return $this->sendResponse([], 'Astrologer not found.');
        }
        $schedulePrice = $astrologer->astrologerSchedule()->first();
        $bookNowPrice = $astrologer->bookNowPrices()->first();
        $settingPrices = $this->getAllPrices();
        $data = [
            "astrologer_id" => $astrologer->id,
            'voice_call_price_30min' => $schedulePrice->audio_call_price_30min ?? $settingPrices['voice_30_min_price'],
            'voice_call_price_60min' => $schedulePrice->audio_call_price_60min ?? $settingPrices['voice_60_min_price'],
            'video_call_price_30min' => $schedulePrice->video_call_price_30min ?? $settingPrices['video_30_min_price'],
            'video_call_price_60min' => $schedulePrice->video_call_price_60min ?? $settingPrices['video_60_min_price'],
            'chat_price' => $bookNowPrice->chat_price ?? $settingPrices['chat_min_price'],
            'voice_price' => $bookNowPrice->voice_price ?? $settingPrices['voice_min_price'],
            'video_price' => $bookNowPrice->video_price ?? $settingPrices['video_min_price'],
        ];
        return $this->sendResponse($data, 'Schedule retrieved successfully.');
    } 

    public function bookAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'astrologer_id' => 'required',
            'date' => 'required',
            'connect_type' => 'required|in:chat,voice,video',
            'duration' => 'required',
            'time_period' => 'required',
            'price' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());      
        }

        $existingAppointment = Appointment::where([
            'astrologer_id' => $request->astrologer_id,
            'time_period' => $request->time_period,
            'date' => $request->date
        ])->exists();

        $ongoingAppointment = Appointment::where([
            'customer_id' => auth('api')->user()->id,
            'time_period' => $request->time_period,
            'date' => $request->date,
        ])->exists();
    
        if ($existingAppointment || $ongoingAppointment) {
            return $this->sendError('Appointment is already booked for the selected time.');
        }

        $durationSeconds = $request->duration * 60;
        list($startTime, $endTime) = explode('-', $request->time_period);

        $bookingId = mt_rand(1000000000, 9999999999);

        while (Appointment::where('booking_id', $bookingId)->exists()) {
            $bookingId = mt_rand(1000000000, 9999999999);
        }
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

        $payment = $api->payment->fetch($request->payment_id);

        if ($payment['status'] === 'captured') {
            // Create Appointment
            try {
                $appointment = Appointment::create([
                    'customer_id' => auth('api')->user()->id,
                    'astrologer_id' => $request->astrologer_id,
                    'booking_id' => $bookingId,
                    'date' => $request->date,
                    'connect_type' => $request->connect_type,
                    'duration' => $request->duration,
                    'duration_second' => $durationSeconds,
                    'time_period' => $request->time_period,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'price' => $request->price,
                    'gst' => $request->gst,
                    'discount' => $request->discount,
                    'total_price' => $request->total_price,
                    'payment_id' => $request->payment_id,
                    'booking_status' => $request->payment_id ? 15 : 16,
                    'service_type' => $request->service_type,
                ]);
            } catch (\Exception $e) {
                Log::error('Appointment creation failed', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
    
            // Create Order
            try {
                $orderId = $this->generateUniqueOrderId();
                Order::create([
                    'order_id' => $orderId,
                    'customer_id' => auth('api')->user()->id,
                    'astrologer_id' => $request->astrologer_id,
                    'typeable_id' => $appointment->id,
                    'typeable_type' => Appointment::class,
                    'price' => $request->price,
                    'gst' => $request->gst,
                    'discount' => $request->discount,
                    'total_price' => $request->total_price,
                    'payment_id' => $request->payment_id,
                    'coupon_id' => $request->coupon_id,
                    'order_status' => $request->payment_id ? 7 : 8,
                ]);
            } catch (\Exception $e) {
                Log::error('Order creation failed', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        
            // Create Astrologer Wallet History
            try {
                AstrologerWalletHistory::create([
                    'astrologer_id' => $request->astrologer_id,
                    'type' => 1,
                    'message' => 'credited to your account for the appointment #' . $appointment->booking_id,
                    'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
                ]);
            } catch (\Exception $e) {
                Log::error('Astrologer Wallet History creation failed', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
        
            // Create Astrologer Earning
            try {
                AstrologerEarning::create([
                    'astrologer_id' => $request->astrologer_id,
                    'appointment_id' => $appointment->id,
                    'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
                ]);
            } catch (\Exception $e) {
                Log::error('Astrologer Earning creation failed', [
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
            }
            $appointment->booking_status_name = Status::find($appointment->booking_status)->name;
            // Send Notification
            // For Customer
            $deviceTokensCustomer = json_decode(auth('api')->user()->device_token);
            $titleCustomer = "Appointment Confirmed";
            $messageCustomer = "Your appointment with ". $appointment->astrologer->full_name ." is confirmed for ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $appointment->start_time .". Be ready for your session.";
            $this->sendNotification($titleCustomer, $messageCustomer, $deviceTokensCustomer);
            Notification::create([
                'user_id' => $appointment->customer->id,
                'title' => $titleCustomer,
                'subtitle' => $messageCustomer,
                'type' => 'general',
            ]);
            // For Expert
            $deviceTokens = json_decode($appointment->astrologer->device_token);
            $title = "New Appointment Scheduled";
            $message = "You have a new appointment with ". $appointment->customer->full_name ." on ". Carbon::parse($appointment->date)->format('d-M-Y') ." at ". $appointment->start_time .". Please be prepared.";
            if (!empty($deviceTokens)) {
                $this->sendNotification($title, $message, $deviceTokens);
            }
            Notification::create([
                'user_id' => $appointment->astrologer->id,
                'title' => $title,
                'subtitle' => $message,
                'type' => 'general',
            ]);
            $astrologer = $appointment->astrologer()->withTrashed()->first();
            $data = [
                'id' => $appointment->id,
                'name' => $astrologer->full_name,
                'profile_picture' => $astrologer->profile_picture,
                'professional_title' => $astrologer->professional_title,
                'zego_user_id' => $astrologer->zego_user_id,
                'is_online' => $astrologer->is_online,
                'connect_type' => $appointment->connect_type,
                'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                'time' => $appointment->time_period,
                'duration' => $appointment->duration,
                'booking_status_name' => Status::find($appointment->booking_status)->name
            ];
            $ref = $this->createFirebaseDatabase()->getReference('orderCount/'.$astrologer->id);
            $existingData = $ref->getValue();
            if ($existingData) {
                $newCount = isset($existingData['count']) ? $existingData['count'] + 1 : 1;
                $ref->update(['count' => $newCount]);
            } else {
                $ref->set([
                    'id' => $astrologer->id,
                    'zego_user_id' => $astrologer->zego_user_id,
                    'count' => 1
                ]);
            }
            return $this->sendResponse($data, 'Appointment Confirmed!');
        } else {
            return $this->sendError('Payment Failed!');
        }
    }

    public function getAstrologerAvailability(Request $request, $id)
    {   
        $currentTime = now()->format('H:i');
        $currentDay = now()->format('l');
        $duration = (int)$request->input('duration', 0);
        $connetType = $request->input('connect_type');
        
        $bookNowPrice = AstrologerBookNowPrice::where('astrologer_id',$id)->first();
        if ($bookNowPrice) {
            switch ($connetType) {
                case 'chat':
                    $price = (int)$bookNowPrice->chat_price * $duration;
                    break;
                case 'voice':
                    $price = (int)$bookNowPrice->voice_price * $duration;
                    break;
                case 'video':
                    $price = (int)$bookNowPrice->video_price * $duration;
                    break;
                default:
                    return $this->sendResponse([
                        'status' => false,
                        'message' => 'Invalid connect type.'
                    ], 'Invalid connect type provided.');
            }
        }

        $astrologer = User::where('id', $id)->where('is_online', 1)->first();

        if (!$astrologer) {
            return $this->sendError('The expert is currently offline.', [
                'status' => false,
                'status_text' => 'offline',
            ]);
        }

        $requestedEndTime = now()->addMinutes($duration)->format('H:i');

        $ongoingAppointment = Appointment::where('customer_id', auth('api')->user()->id)
            ->where('astrologer_id', '!=', $id)
            ->where('date', now()->toDateString())
            ->where('start_time', '<', $requestedEndTime)
            ->where('end_time', '>', $currentTime)
            ->where('booking_status', 15)
            ->first();

        if ($ongoingAppointment) {
            $astrologerName = $ongoingAppointment->astrologer->full_name;
            $appointmentType = $ongoingAppointment->connect_type;
            $message = match ($appointmentType) {
                'video' => "You are already on a video call with {$astrologerName}",
                'voice' => "You are already on a voice call with {$astrologerName}",
                default  => "You are already chatting with {$astrologerName}",
            };
            return $this->sendError($message, [
                'status' => false,
                'status_text' => 'busy_with_another_astrologer',
                // 'conflicting_appointment' => $ongoingAppointment,
            ]);
        }

        $currentAppointment = Appointment::where('astrologer_id', $id)
            ->where('date', now()->toDateString())
            ->where(function ($query) use ($requestedEndTime) {
                $query->where('start_time', '<', $requestedEndTime)
                      ->where('booking_status', 15)
                      ->where('end_time', '>', now()->format('H:i'));
            })
            ->first();
        if ($currentAppointment) {
            $endTime = Carbon::parse($currentAppointment->end_time);
            
            $nextAppointment = Appointment::where('astrologer_id', $id)
                ->where('date', now()->toDateString())
                ->where('start_time', '>=', $endTime->format('H:i'))
                ->orderBy('start_time', 'asc')
                ->first();

            if ($nextAppointment) {
                $nextAvailableTime = Carbon::parse($nextAppointment->end_time);
                $waitingTime = now()->diffInMinutes($nextAvailableTime);
            } else {
                $nextAvailableTime = $endTime;
                $waitingTime = now()->diffInMinutes($endTime);
            }

            $hours = floor($waitingTime / 60);
            $minutes = $waitingTime % 60;
            $formattedWaitingTime = $hours > 0 ? "{$hours} hr {$minutes} min" : "{$minutes} min";

            return $this->sendResponse([
                'status' => false,
                'status_text' => 'busy',
                'waiting_time' => $formattedWaitingTime,
                'next_available_time' => $nextAvailableTime,
                'price' => $price,
            ], 'The expert is currently busy. Next available time is ' . $nextAvailableTime->format('H:i'));
        }

        return $this->sendResponse([
            'status' => true,
            'status_text' => 'available',
            'price' => $price,
        ], 'The expert is available.');

        // $schedule = AstrologerSchedule::where('astrologer_id', $id)
        //             ->where('is_availability', 1)
        //             ->first();

        // if ($schedule) {
        //     $schedules = json_decode($schedule->schedule, true);
        //     $todaySchedule = collect($schedules)->firstWhere('day', $currentDay);

        //     if ($todaySchedule && isset($todaySchedule['time_periods'])) {
        //         $requestedEndTime = now()->addMinutes($duration)->format('H:i');

        //         foreach ($todaySchedule['time_periods'] as $timePeriod) {
        //             $startTime = $timePeriod['start_time'];
        //             $endTime = $timePeriod['end_time'];

        //             if ($currentTime >= $startTime && $requestedEndTime <= $endTime) {
        //                 $isBusy = Appointment::where('astrologer_id', $id)
        //                     ->where('date', now()->toDateString())
        //                     ->where(function ($query) use ($currentTime, $requestedEndTime) {
        //                         $query->where(function ($q) use ($currentTime, $requestedEndTime) {
        //                             $q->where('start_time', '<', $requestedEndTime)
        //                             ->where('end_time', '>', $currentTime);
        //                         });
        //                     })
        //                     ->first();
        //                     if ($isBusy) {
        //                         $endTime = Carbon::parse($isBusy->end_time);
        //                         $nextAppointment = Appointment::where('astrologer_id', $id)
        //                             ->where('date', now()->toDateString())
        //                             ->where('start_time', '>=', $endTime->format('H:i'))
        //                             ->orderBy('start_time', 'asc')
        //                             ->first();

        //                         if ($nextAppointment) {
        //                             $nextStartTime = Carbon::parse($nextAppointment->start_time);
        
        //                             // Check if requested slot fits before the next appointment
        //                             if ($endTime->copy()->addMinutes($duration)->lte($nextStartTime)) {
        //                                 return $this->sendResponse([
        //                                     'status' => true,
        //                                     'status_text' => 'available',
        //                                     'price' => $price,
        //                                 ], 'The expert is available.');
        //                             }
        //                             $waitingTime = now()->diffInMinutes(Carbon::parse($nextAppointment->end_time));
        //                             $roundedWaitingTime = (int) round($waitingTime);
        //                             $nextAvailableTime = Carbon::parse($nextAppointment->end_time);
        //                         } else {
        //                             $waitingTime = now()->diffInMinutes($endTime);
        //                             $roundedWaitingTime = (int) round($waitingTime);
        //                             $nextAvailableTime = $endTime;
        //                         }
        //                         $hours = floor($roundedWaitingTime / 60);
        //                         $minutes = $roundedWaitingTime % 60;
                                
        //                         if ($hours > 0) {
        //                             $formattedWaitingTime = "{$hours} hour" . ($hours > 1 ? "s" : "");
        //                             if ($minutes > 0) {
        //                                 $formattedWaitingTime .= " and {$minutes} minute" . ($minutes > 1 ? "s" : "");
        //                             }
        //                         } else {
        //                             $formattedWaitingTime = "{$minutes} minutes";
        //                         }
        //                         foreach ($todaySchedule['time_periods'] as $period) {
        //                             if ($nextAvailableTime->format('H:i') >= $period['start_time'] && $nextAvailableTime->format('H:i') < $period['end_time']) {
        //                                 return $this->sendResponse([
        //                                     'status' => false,
        //                                     'status_text' => 'busy',
        //                                     'waiting_time' => $formattedWaitingTime,
        //                                     'next_available_time' => $nextAvailableTime,
        //                                     'price' => $price,
        //                                 ], 'The expert is currently busy. Next available time is ' . $nextAvailableTime->format('H:i'));
        //                             }
        //                         }
        //                     } else {
        //                         return $this->sendResponse([
        //                             'status' => true,
        //                             'status_text' => 'available',
        //                             'price' => $price,
        //                         ], 'The expert is available.');
        //                     }
        //             }
        //         }

        //         return $this->sendError('The expert is not available at this time.', [
        //             'status' => false,
        //             'status_text' => 'not_available',
        //         ]);
        //     }
        // }
    }

    public function bookNow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'astrologer_id' => 'required',
            'connect_type' => 'required|in:chat,voice,video',
            'duration' => 'required|in:15,30,45,60',
            'price' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());      
        }

        if ($request->payment_id === 'freeChat') {
            return DB::transaction(function () use ($request) {
                return $this->createFreeChatAppointment($request);
            });
        }
        
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $payment = $api->payment->fetch($request->payment_id);

        if ($payment['status'] === 'captured') {
            return DB::transaction(function () use ($request) {
                return $this->createPaidAppointment($request);
            });
        }
        return $this->sendError('Payment Failed!'); 
    }

    private function createFreeChatAppointment($request)
    {
        $startTime = $request->start_time ?? Carbon::now()->format('H:i');
        $durationSeconds = $request->duration * 60;
        $endTime = Carbon::parse($startTime)->addMinutes($request->duration)->format('H:i');
        $timePeriod = $startTime . '-' . $endTime;

        $overlapExists = Appointment::where('astrologer_id', $request->astrologer_id)
            ->whereDate('date', now()->toDateString())
            ->where('booking_status', '!=', 17)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            })
            ->lockForUpdate()
            ->exists();

        if ($overlapExists) {
            return $this->sendError('This astrologer is already booked for the selected time.');
        }

        $bookingId = mt_rand(1000000000, 9999999999);
        while (Appointment::where('booking_id', $bookingId)->exists()) {
            $bookingId = mt_rand(1000000000, 9999999999);
        }

        $appointment = Appointment::create([
            'customer_id' => auth('api')->user()->id,
            'astrologer_id' => $request->astrologer_id,
            'booking_id' => $bookingId,
            'date' => now()->toDateString(),
            'connect_type' => $request->connect_type,
            'duration' => $request->duration,
            'duration_second' => $durationSeconds,
            'time_period' => $timePeriod,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price' => $request->price,
            'gst' => $request->gst,
            'discount' => $request->discount,
            'total_price' => $request->total_price,
            'payment_id' => 'freeChat',
            'booking_status' => 15,
            'is_waiting' => $request->start_time ? 1 : 0,
        ]);

        $astrologerBookNowPrice = AstrologerBookNowPrice::where('astrologer_id', $request->astrologer_id)->lockForUpdate()->first();
        if ($astrologerBookNowPrice && $astrologerBookNowPrice->available_credits > 0) {
            $astrologerBookNowPrice->available_credits -= 1;
            $astrologerBookNowPrice->save();
        }

        $orderId = $this->generateUniqueOrderId();
        Order::create([
            'order_id' => $orderId,
            'customer_id' => auth('api')->user()->id,
            'astrologer_id' => $request->astrologer_id,
            'typeable_id' => $appointment->id,
            'typeable_type' => Appointment::class,
            'price' => $request->price,
            'gst' => $request->gst,
            'discount' => $request->discount,
            'total_price' => $request->total_price,
            'payment_id' => 'freeChat',
            'coupon_id' => $request->coupon_id,
            'order_status' => 7,
        ]);
        AstrologerEarning::create([
            'astrologer_id' => $request->astrologer_id,
            'appointment_id' => $appointment->id,
            'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
        ]);
        User::where('id', auth('api')->user()->id)->update([
            'free_chat_used' => 1,
        ]);

        return $this->finalizeAppointment($appointment);
    }
    private function createPaidAppointment($request)
    {
        $startTime = $request->start_time ?? Carbon::now()->format('H:i');
        $durationSeconds = $request->duration * 60;
        $endTime = Carbon::parse($startTime)->addMinutes($request->duration)->format('H:i');
        $timePeriod = $startTime . '-' . $endTime;

        $overlapExists = Appointment::where('astrologer_id', $request->astrologer_id)
            ->whereDate('date', now()->toDateString())
            ->where('booking_status', '!=', 17)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                ->orWhereBetween('end_time', [$startTime, $endTime])
                ->orWhere(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            })
            ->lockForUpdate()
            ->exists();

        if ($overlapExists) {
            return $this->sendError('This astrologer is already booked for the selected time.');
        }

        $bookingId = mt_rand(1000000000, 9999999999);
        while (Appointment::where('booking_id', $bookingId)->exists()) {
            $bookingId = mt_rand(1000000000, 9999999999);
        }

        // Create Appointment
        try {
            $appointment = Appointment::create([
                'customer_id' => auth('api')->user()->id,
                'astrologer_id' => $request->astrologer_id,
                'booking_id' => $bookingId,
                'date' => now()->toDateString(),
                'connect_type' => $request->connect_type,
                'duration' => $request->duration,
                'duration_second' => $durationSeconds,
                'time_period' => $timePeriod,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'price' => $request->price,
                'gst' => $request->gst,
                'discount' => $request->discount,
                'total_price' => $request->total_price,
                'payment_id' => $request->payment_id,
                'booking_status' => 15,
                'is_waiting' => $request->start_time ? 1 : 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Appointment creation failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }

        // Create Order
        try {
            $orderId = $this->generateUniqueOrderId();
            Order::create([
                'order_id' => $orderId,
                'customer_id' => auth('api')->user()->id,
                'astrologer_id' => $request->astrologer_id,
                'typeable_id' => $appointment->id,
                'typeable_type' => Appointment::class,
                'price' => $request->price,
                'gst' => $request->gst,
                'discount' => $request->discount,
                'total_price' => $request->total_price,
                'payment_id' => $request->payment_id,
                'coupon_id' => $request->coupon_id,
                'order_status' => 7,
            ]);
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }

        // Create Astrologer Wallet History
        try {
            AstrologerWalletHistory::create([
                'astrologer_id' => $request->astrologer_id,
                'type' => 1,
                'message' => 'credited to your account for the appointment #' . $appointment->booking_id,
                'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
            ]);
        } catch (\Exception $e) {
            Log::error('Astrologer wallet history creation failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }

        // Create Astrologer Earning
        try {
            AstrologerEarning::create([
                'astrologer_id' => $request->astrologer_id,
                'appointment_id' => $appointment->id,
                'amount' => $request->total_price * (env('EXPERT_COMMISSION', 50) / 100),
            ]);
        } catch (\Exception $e) {
            Log::error('Astrologer earning creation failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }

        return $this->finalizeAppointment($appointment);
    }
    private function finalizeAppointment($appointment)
    {
        $appointment->booking_status_name = Status::find($appointment->booking_status)->name;

        // Customer notification
        $deviceTokensCustomer = json_decode($appointment->customer->device_token);
        $titleCustomer = "Appointment Confirmed";
        $messageCustomer = "Your appointment with " . $appointment->astrologer->full_name . " is confirmed for " . Carbon::parse($appointment->date)->format('d-M-Y') . " at " . $appointment->start_time . ". Be ready for your session.";
        
        if(!empty($deviceTokensCustomer)) {
            $this->sendNotification($titleCustomer, $messageCustomer, $deviceTokensCustomer);
        }

        Notification::create([
            'user_id' => $appointment->customer_id,
            'title' => $titleCustomer,
            'subtitle' => $messageCustomer,
            'type' => 'general',
        ]);

        // Expert notification
        $deviceTokens = json_decode($appointment->astrologer->device_token);
        $title = "New Appointment Scheduled";
        $message = "You have a new appointment with " . $appointment->customer->full_name . " on " . Carbon::parse($appointment->date)->format('d-M-Y') . " at " . $appointment->start_time . ". Please be prepared.";

        if (!empty($deviceTokens)) {
            $this->sendNotification($title, $message, $deviceTokens);
        }

        Notification::create([
            'user_id' => $appointment->astrologer->id,
            'title' => $title,
            'subtitle' => $message,
            'type' => 'general',
        ]);

        $astrologer = $appointment->astrologer()->withTrashed()->first();
        $data = [
            'id' => $appointment->id,
            'name' => $astrologer->full_name,
            'profile_picture' => $astrologer->profile_picture,
            'professional_title' => $astrologer->professional_title,
            'zego_user_id' => $astrologer->zego_user_id,
            'is_online' => $astrologer->is_online,
            'connect_type' => $appointment->connect_type,
            'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
            'time' => $appointment->time_period,
            'duration' => $appointment->duration,
            'booking_status_name' => Status::find($appointment->booking_status)->name,
        ];

        $ref = $this->createFirebaseDatabase()->getReference('orderCount/' . $astrologer->id);
        $existingData = $ref->getValue();
        if ($existingData) {
            $newCount = isset($existingData['count']) ? $existingData['count'] + 1 : 1;
            $ref->update(['count' => $newCount]);
        } else {
            $ref->set([
                'id' => $astrologer->id,
                'zego_user_id' => $astrologer->zego_user_id,
                'count' => 1
            ]);
        }

        return $this->sendResponse($data, 'Appointment Confirmed!');
    }

    public function bookingStatusChangetoComplete(Request $request)
    {
        $bookingStatus = Appointment::where('id', $request->appointment_id)->where('booking_status', 15)->first();
        if(!$bookingStatus)
        {
            return $this->sendResponse([], 'Appointment not found');
        }
        $bookingStatus->update(['booking_status' => 17]);
        $bookingStatus->booking_status_name = Status::find($bookingStatus->booking_status)->name ;
        return $this->sendResponse($bookingStatus, 'Booking status changed successfully.');
    }

    public function getAppointments(Request $request)
    {
        $now = Carbon::now();
        $todayDate = $now->toDateString();
        $currentTime = $now->toTimeString();

        $appointments = Appointment::where(function ($query) use ($todayDate, $currentTime) {
                $query->where('date', '<', $todayDate)
                    ->orWhere(function ($q) use ($todayDate, $currentTime) {
                        $q->where('date', '=', $todayDate)
                            ->where('end_time', '<', $currentTime);
                    });
            })
            ->where('booking_status', 15)
            ->get();

        Appointment::whereIn('id', $appointments->pluck('id'))
            ->update(['booking_status' => 17]);
        AstrologerEarning::whereHas('appointment', function ($query) {
                $query->where('booking_status', 17);
            })
            ->whereIn('appointment_id', $appointments->pluck('id'))
            ->update(['status' => 1]);

        foreach ($appointments as $appointment) {
            $ref = $this->createFirebaseDatabase()->getReference('orderCount/'.$appointment->astrologer_id);
            $existingData = $ref->getValue();

            if ($existingData && isset($existingData['count']) && $existingData['count'] > 0) {
                $newCount = $existingData['count'] - 1;
                $ref->update(['count' => $newCount]);
            }
        }
        $appointments = Appointment::where('customer_id', auth('api')->user()->id)
            ->where('booking_status',15)
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate($request->per_page);
        if($appointments->isEmpty())
        {
            return $this->sendResponse([], 'Appointments not found');
        }
        $data = $appointments->groupBy(function ($appointment) {
            $date = Carbon::parse($appointment->date);
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($appointment) {
                    $astrologer = $appointment->astrologer()->withTrashed()->first();
                    return [
                        'id' => $appointment->id,
                        'name' => $astrologer->full_name,
                        'profile_picture' => $astrologer->profile_picture,
                        'professional_title' => $astrologer->professional_title,
                        'zego_user_id' => $astrologer->zego_user_id,
                        'is_online' => $astrologer->is_online,
                        'connect_type' => $appointment->connect_type,
                        'service_type' => $appointment->service_type,
                        'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                        'time' => $appointment->time_period,
                        'duration' => $appointment->duration,
                        'booking_status_name' => Status::find($appointment->booking_status)->name
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Appointments retrived successfully!', $appointments);
    }

    public function getCompletedAppointments(Request $request)
    {
        $appointments = Appointment::where('customer_id', auth('api')->user()->id)
            ->where('booking_status',17)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->paginate($request->per_page);
        if($appointments->isEmpty())
        {
            return $this->sendResponse([], 'Appointments not found');
        }
        $data = $appointments->groupBy(function ($appointment) {
            $date = Carbon::parse($appointment->date);
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($appointment) {
                    $astrologer = $appointment->astrologer()->withTrashed()->first();
                    $call_time = CallLog::where('appointment_id', $appointment->id)->first()?->call_time;
                    return [
                        'id' => $appointment->id,
                        'name' => $astrologer->full_name,
                        'profile_picture' => $astrologer->profile_picture,
                        'professional_title' => $astrologer->professional_title,
                        'is_online' => $astrologer->is_online,
                        'connect_type' => $appointment->connect_type,
                        'service_type' => $appointment->service_type,
                        'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                        'time' => $appointment->time_period,
                        'call_time' => $call_time,
                        'booking_status_name' => Status::find($appointment->booking_status)->name
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Appointments retrived successfully!', $appointments);
    }

    public function astrologerReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'astrologer_id' => 'required',
            'ratings' => 'required',
            'review' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $existingReview = AstrologerRating::where('user_id', auth('api')->user()->id)
                                ->where('astrologer_id', $request->astrologer_id)
                                ->first();

        if ($existingReview) {
            return $this->sendError('You are already reviewed this astrologer');
        }

        $data = AstrologerRating::create([
            'user_id' => auth('api')->user()->id,
            'astrologer_id' => $request->astrologer_id,
            'ratings' => $request->ratings,
            'review' => $request->review,
        ]);
        
        return $this->sendResponse($data, 'Review added successfully');
    }

    public function getAstrologerReviews(Request $request)
    {
        // $astrologer_id = $request->astrologer_id;
        // $reviews = AstrologerRating::where('astrologer_id',$astrologer_id)->paginate($request->per_page);
        // if($reviews->isEmpty()){
        //     return $this->sendResponse([], 'Reviews not found');
        // }
        // $data = $reviews->map(function ($review) {
        //     $user = $review->user()->withTrashed()->first();
        //     return[
        //         'id' => $review->id,
        //         'review' => $review->review,
        //         'ratings' => $review->ratings,
        //         'name' => $user->full_name,
        //         'profile_picture' => $user->profile_picture,
        //     ];
        // });
        $astrologer_id = $request->astrologer_id;
        $astrologerReviews = AstrologerRating::where('astrologer_id', $astrologer_id)->get();
        $appointmentIds = Appointment::where('astrologer_id', $astrologer_id)->pluck('id');
        $appointmentReviews = AppointmentRating::whereHas('appointment', function ($query) use ($appointmentIds) {
            $query->whereIn('id', $appointmentIds);
        })
        ->orderBy('created_at', 'desc')
        ->get()
        ->unique('user_id');
        $reviews = $astrologerReviews->merge($appointmentReviews);

        if ($reviews->isEmpty()) {
            return $this->sendResponse([], 'Reviews not found');
        }

        $data = $reviews->map(function ($review) {
            $user = $review->user()->withTrashed()->first();
            return [
                'id' => $review->id,
                'review' => $review->review,
                'ratings' => $review->ratings,
                'name' => $user->full_name ?? 'Unknown',
                'profile_picture' => $user->profile_picture ?? null,
            ];
        });

        $totalReviews = $reviews->count();

        $response = [
            'total_reviews' => $totalReviews,
            'reviews' => $data,
        ];
        return $this->sendResponse($response, 'Review retrived successfully');
    }
    public function appointmentReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'appointment_id' => 'required',
            'ratings' => 'required',
            'review' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $appointment_id = null;
        if ($request->has('call_id')) {
            $callLog = CallLog::where('call_id', $request->call_id)->first();
            if ($callLog) {
                $appointment_id = $callLog->appointment_id;
            } else {
                return $this->sendError('Invalid call_id');
            }
        }
        elseif ($request->has('appointment_id')) {
            $appointment_id = $request->appointment_id;
        }

        if (!$appointment_id) {
            return $this->sendError('No valid appointment_id found');
        }
        $existingReview = AppointmentRating::where('user_id', auth('api')->user()->id)
                                ->where('appointment_id', $request->appointment_id)
                                ->first();

        if ($existingReview) {
            return $this->sendError('You are already reviewed this appointment');
        }

        $data = AppointmentRating::create([
            'user_id' => auth('api')->user()->id,
            'appointment_id' => $appointment_id,
            'ratings' => $request->ratings,
            'review' => $request->review,
        ]);
        
        return $this->sendResponse($data, 'Review added successfully');
    }
    public function getAppointmentReviews(Request $request)
    {
        $appointment_id = $request->appointment_id;
        $reviews = AppointmentRating::where('appointment_id',$appointment_id)->paginate($request->per_page);
        if($reviews->isEmpty()){
            return $this->sendResponse([], 'Reviews not found');
        }
        $data = $reviews->map(function ($review) {
            $user = $review->user()->withTrashed()->first();
            return[
                'id' => $review->id,
                'review' => $review->review,
                'ratings' => $review->ratings,
                'name' => $user->full_name,
                'profile_picture' => $user->profile_picture,
            ];
        });
        $totalReviews = $reviews->count();

        $response = [
            'total_reviews' => $totalReviews,
            'reviews' => $data,
        ];
        return $this->sendResponse($response, 'Review retrived successfully', $reviews);
    }
    public function getOngoingAppointments(Request $request)
    {
        $user = auth('api')->user();
        $query = Appointment::query()
            ->where('date', now()->toDateString())
            ->where('booking_status', 15)
            ->where('start_time', '<=', now()->format('H:i'))
            ->where('end_time', '>=', now()->format('H:i'));

        if ($user->hasRole('customer')) {
            $query->where('customer_id', $user->id);
        } elseif ($user->hasRole('astrologer')) {
            $query->where('astrologer_id', $user->id);
        }

        $appointment = $query->first();
        if (!$appointment) {
            return $this->sendResponse((object) [], 'Ongoing Appointments not found');
        }
        $user = $user->hasRole('customer') ? $appointment->astrologer()->withTrashed()->first() : $appointment->customer()->withTrashed()->first();
        $data = [
            'id' => $appointment->id,
            'name' => $user->full_name,
            'profile_picture' => $user->profile_picture,
            'zego_user_id' => $user->zego_user_id,
            'connect_type' => $appointment->connect_type,
            'service_type' => $appointment->service_type,
            'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
            'time' => $appointment->time_period,
            'duration' => $appointment->duration,
            'booking_status_name' => Status::find($appointment->booking_status)->name
        ];

        return $this->sendResponse($data, 'Ongoing Appointments retrieved successfully!');
    }
    // Astrologer
    public function getAstroAppointments(Request $request)
    {
        $appointments = Appointment::where('astrologer_id', auth('api')->user()->id)
            ->where('booking_status',15)
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate($request->per_page);
        if($appointments->isEmpty())
        {
            return $this->sendResponse([], 'Appointments not found');
        }
        $data = $appointments->groupBy(function ($appointment) {
            $date = Carbon::parse($appointment->date);
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($appointment) {
                    $customer = $appointment->customer()->withTrashed()->first();
                    return [
                        'id' => $appointment->id,
                        'name' => $customer->full_name,
                        'profile_picture' => $customer->profile_picture,
                        'zego_user_id' => $customer->zego_user_id,
                        'connect_type' => $appointment->connect_type,
                        'service_type' => $appointment->service_type,
                        'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                        'time' => $appointment->time_period,
                        'duration' => $appointment->duration,
                        'booking_status_name' => Status::find($appointment->booking_status)->name
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Appointments retrived successfully!', $appointments);
    }

    public function getAstroCompletedAppointments(Request $request)
    {
        $appointments = Appointment::where('astrologer_id', auth('api')->user()->id)
                            ->where('booking_status',17)
                            ->orderByDesc('date')
                            ->orderByDesc('start_time')
                            ->paginate($request->per_page);
        if($appointments->isEmpty())
        {
            return $this->sendResponse([], 'Appointments not found');
        }
        $data = $appointments->groupBy(function ($appointment) {
            $date = Carbon::parse($appointment->date);
                if ($date->isToday()) {
                    return 'Today';
                } elseif ($date->isYesterday()) {
                    return 'Yesterday';
                } else {
                    return $date->format('d-M-Y');
                }
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($appointment) {
                    $customer = $appointment->customer()->withTrashed()->first();
                    $call_time = CallLog::where('appointment_id', $appointment->id)->first()?->call_time;
                    return [
                        'id' => $appointment->id,
                        'name' => $customer->full_name,
                        'profile_picture' => $customer->profile_picture,
                        'professional_title' => $customer->professional_title,
                        'connect_type' => $appointment->connect_type,
                        'service_type' => $appointment->service_type,
                        'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                        'time' => $appointment->time_period,
                        'call_time' => $call_time,
                        'booking_status_name' => Status::find($appointment->booking_status)->name
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Appointments retrived successfully!', $appointments);
    }

    public function getAssignedCustomers(Request $request)
    {
        $assignedCustomers = Appointment::where('astrologer_id', auth('api')->user()->id)->paginate($request->per_page);
        if($assignedCustomers->isEmpty())
        {
            return $this->sendResponse([], 'Assigned customer not found');
        }
        $data = $assignedCustomers->map(function ($assignedCustomer) {
            $customer = $assignedCustomer->customer()->withTrashed()->first();
            $totalAppointments = $assignedCustomer->where('customer_id',$customer->id)->count();
            return[
                'id' => $assignedCustomer->id,
                'customer_id' => $customer->id,
                'name' => $customer->full_name,
                'profile_picture' => $customer->profile_picture,
                'date' => Carbon::parse($assignedCustomer->date)->format('d-M-Y'),
                'total_appointments' => $totalAppointments,
                
            ];
        });
        $data = $data->unique('customer_id')->values();
        return $this->sendResponse($data, 'Assigned customer retrived successfully!', $assignedCustomers);
    }
    public function getConsultationHistory(Request $request)
    {
        $histories = Appointment::where('astrologer_id', auth('api')->user()->id)->paginate($request->per_page);
        if($histories->isEmpty())
        {
            return $this->sendResponse([], 'Assigned customer not found');
        }
        $data = $histories->map(function ($history) {
            $customer = $history->customer()->withTrashed()->first();
            $totalAppointments = $history->where('customer_id',$customer->id)->count();
            return[
                'id' => $history->id,
                'customer_id' => $customer->id,
                'name' => $customer->full_name,
                'connect_type' => $history->connect_type,
                'service_type' => $appointment->service_type,
                'profile_picture' => $customer->profile_picture,
                'date' => Carbon::parse($history->date)->format('d-M-Y'),
                'time' => Carbon::parse($history->start_time)->format('H:i'),
            ];
        });
        return $this->sendResponse($data, 'Consultation history retrived successfully!', $histories);
    }
    
    public function getOngoingChat()
    {
        $currentTime = now()->format('H:i:s');
        $appointment = Appointment::where('customer_id', auth('api')->user()->id)
            ->where('date', now()->toDateString())
            ->where('start_time', '<', $currentTime)
            ->where('end_time', '>', $currentTime)
            ->where('booking_status', 15)
            ->where('connect_type', 'chat')
            ->first();
        if (!$appointment) {
            return $this->sendResponse((object) [], 'Ongoing Chat not found');
        }
        $user = $appointment->astrologer()->withTrashed()->first();
        $data = [
            'id' => $appointment->id,
            'name' => $user->full_name,
            'profile_picture' => $user->profile_picture,
            'zego_user_id' => $user->zego_user_id,
            'connect_type' => $appointment->connect_type,
            'service_type' => $appointment->service_type,
            'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
            'time' => $appointment->time_period,
            'duration' => $appointment->duration,
            'booking_status_name' => Status::find($appointment->booking_status)->name
        ];
        return $this->sendResponse($data, 'Ongoing chat retrived successfully!');
    }

    public function getFreeClaimChat()
    {
        $orderCount = Order::where('customer_id', auth('api')->user()->id)->where('typeable_type','App\Models\Appointment')->count();
        $data = [
            'is_free_chat_claimed' => false,
            // 'is_free_chat_claimed' => $orderCount === 0 ? true : false,
        ];
        return $this->sendResponse($data, 'Free claim chat retrived successfully!');
    }

    public function extendTenMinChat(Request $request)
    {
        $appointment = Appointment::where('id', $request->appointment_id)->first();
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }
        $endTime = Carbon::parse($appointment->end_time);
        $nextAppointment = Appointment::where('astrologer_id', auth('api')->user()->id)
            ->where('date', now()->toDateString())
            ->where('start_time', '>=', $endTime->format('H:i'))
            ->where('booking_status', 15)
            ->orderBy('start_time', 'asc')
            ->first();
        if ($nextAppointment) {
            $nextStartTime = Carbon::parse($nextAppointment->start_time);
            $differenceInMinutes = $endTime->diffInMinutes($nextStartTime, false);
        
            if ($differenceInMinutes > 10) {
                $newDuration = $appointment->duration + 10;
                $newDurationSeconds = $appointment->duration_second + 600;
                $newEndTime = $endTime->addMinutes(10);
                $startTime = Carbon::parse($appointment->start_time);
                $newTimePeriod = $startTime->format('H:i') . '-' . $newEndTime->format('H:i');
        
                $appointment->update([
                    'duration' => $newDuration,
                    'duration_second' => $newDurationSeconds,
                    'time_period' => $newTimePeriod,
                    'end_time' => $newEndTime->format('H:i'),
                    'is_extended_chat' => 1,
                ]);
                if ($request->appointment_id) {
                    $ref = $this->createFirebaseDatabase()->getReference('chatStatus/'.$request->appointment_id);
                    $existingData = $ref->getValue();
                    if ($existingData) {
                        $ref->update([
                            'time' => $startTime->format('H:i') . '-' . $newEndTime->format('H:i'),
                            'isExtended' => true,
                        ]);
                    }
                }
        
                return response()->json(['message' => 'Appointment updated successfully.']);
            } else {
                return response()->json(['error' => 'Next appointment starts in less than 10 minutes.'], 400);
            }
        } else {
            $newDuration = $appointment->duration + 10;
            $newDurationSeconds = $appointment->duration_second + 600;
            $endTime = Carbon::parse($appointment->end_time);
            $newEndTime = $endTime->addMinutes(10);
            $startTime = Carbon::parse($appointment->start_time);
            $newTimePeriod = $startTime->format('H:i') . '-' . $newEndTime->format('H:i');

            $appointment->update([
                'duration' => $newDuration,
                'duration_second' => $newDurationSeconds,
                'time_period' => $newTimePeriod,
                'end_time' => $newEndTime->format('H:i'),
                'is_extended_chat' => 1,
            ]);
            if ($request->appointment_id) {
                $ref = $this->createFirebaseDatabase()->getReference('chatStatus/'.$request->appointment_id);
                $existingData = $ref->getValue();
                if ($existingData) {
                    $ref->update([
                        'time' => $startTime->format('H:i') . '-' . $newEndTime->format('H:i'),
                        'isExtended' => true,
                    ]);
                }
            }
            return response()->json(['message' => 'Appointment updated successfully.']);
        }
    }
}
