<?php

namespace App\Http\Controllers\API\v2;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Str;
use App\Models\ExpertReferral;
use App\Models\AstrologerRating;
use App\Models\AppointmentRating;
use App\Http\Controllers\API\BaseController;
use App\Models\Product;

class CustomerController extends BaseController
{
    public function getAstrologerDetail($id)
    {
        $user = auth('api')->user(); 
        $showProducts = $user && $user->hasRole('customer');

        $astrologer = User::role('astrologer')
            ->where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('zego_user_id', $id);
            })
            ->where('status', 1)
            ->first();
        if($astrologer === NULL){
            return $this->sendResponse([], 'Astologer detail not found');
        }
        $astrologerAvgRating = AstrologerRating::where('astrologer_id', $astrologer->id)->avg('ratings') ?? 0;
        $astrologerTotalRatings = AstrologerRating::where('astrologer_id', $astrologer->id)->count();
        $appointmentIds = Appointment::where('astrologer_id', $astrologer->id)->pluck('id');

        $appointmentAvgRating = AppointmentRating::whereIn('appointment_id', $appointmentIds)->avg('ratings') ?? 0;
        $appointmentTotalRatings = AppointmentRating::whereIn('appointment_id', $appointmentIds)->count();
        $totalRatings = $astrologerTotalRatings + $appointmentTotalRatings;
        $combinedAvgRating = $totalRatings > 0 
            ? (($astrologerAvgRating * $astrologerTotalRatings) + ($appointmentAvgRating * $appointmentTotalRatings)) / $totalRatings 
            : 0;
        $currentDay = Carbon::now()->format('l');
        $schedule = $astrologer->astrologerSchedule()->first();
        $availabilities = [];
        if ($schedule && $schedule->schedule) {
            $scheduleData = json_decode($schedule->schedule, true);
            $todaySchedule = collect($scheduleData)->firstWhere('day', $currentDay);
            if ($todaySchedule) {
                foreach ($todaySchedule['time_periods'] as $timePeriod) {
                    $availabilities[] = Carbon::parse($timePeriod['start_time'])->format('h A').' - '.Carbon::parse($timePeriod['end_time'])->format('h A');
                }
            }
        }
        $availability = implode(', ', $availabilities);
        $bookNowPrice = $astrologer->bookNowPrices()->first();
        $settingPrices = $this->getAllPrices();
        $currentTime = now()->format('H:i');
        $isBusy = Appointment::where('astrologer_id', $astrologer->id)
            ->where('date', now()->toDateString())
            ->where('end_time', '>', $currentTime)
            ->where('booking_status', 15)
            ->orderBy('start_time', 'asc')
            ->first();
        $endTime = Carbon::parse($isBusy?->end_time);
        $waitingTime = now()->diffInMinutes($endTime);
        $roundedWaitingTime = (int) round($waitingTime);
        $waitingTimeInSeconds = $roundedWaitingTime * 60;
        $products = Product::where('status',1)->get();
        $existing = ExpertReferral::where('astrologer_id', $astrologer->id)->first();
        if (!$existing) {
            $existing = ExpertReferral::create([
                'astrologer_id' => $astrologer->id,
                'referral_code' => strtoupper(Str::random(8)),
            ]);
        }
        $data = [
            'id' => $astrologer->id,
            'name' => $astrologer->full_name,
            'profile_picture' => $astrologer->profile_picture,
            'cut_out_image' => $astrologer->cut_out_image,
            'average_rating' => number_format($combinedAvgRating,2),
            'total_review' => $totalRatings,
            'professional_title' => str_replace(',', ', ', $astrologer->professional_title),
            'description' => $astrologer->description,
            'expertise' => $astrologer->expertise,
            'philosophy' => $astrologer->philosophy,
            'experience' => $astrologer->experience,
            'language' => $astrologer->language,
            'response_time' => $astrologer->response_time,
            'is_online' => $astrologer->is_online,
            'waiting_time_second' => $waitingTimeInSeconds <= 900 ? $waitingTimeInSeconds : 0 ,
            'availability' => !empty($availability) ? $availability : null,
            'voice_call_price_30min' => $schedule->audio_call_price_30min ?? $settingPrices['voice_30_min_price'],
            'voice_call_price_60min' => $schedule->audio_call_price_60min ?? $settingPrices['voice_60_min_price'],
            'video_call_price_30min' => $schedule->video_call_price_30min ?? $settingPrices['video_30_min_price'],
            'video_call_price_60min' => $schedule->video_call_price_60min ?? $settingPrices['video_60_min_price'],
            'chat_price' => $bookNowPrice->chat_price ?? $settingPrices['chat_min_price'],
            'voice_price' => $bookNowPrice->voice_price ?? $settingPrices['voice_min_price'],
            'video_price' => $bookNowPrice->video_price ?? $settingPrices['video_min_price'],
            'share_link' => url('/expertInfoScreen?ref=' . $existing->referral_code.'&id='. $existing->astrologer_id),
        ];
        if ($showProducts) {
            $data['products'] = $products->map(function ($product){
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'type' => $product->type,
                    'duration' => $product->duration,
                    'duration_in_min' => $product->duration_in_min,
                    'description' => $product->description,
                    'price' => $product->price,
                    'is_gst' => $product->is_gst,
                    'gst_type' => $product->gst_type,
                    'gst_amount' => $product->gst_amount,
                    'total_price' => $product->total_price ?? $product->price,
                ];
            });
        }
        return $this->sendResponse($data, 'Astologer detail retrived successfully.');
    }
}
