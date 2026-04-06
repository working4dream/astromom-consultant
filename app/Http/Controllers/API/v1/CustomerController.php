<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\BaseController;
use App\Models\Appointment;
use App\Models\AppointmentRating;
use App\Models\AstrologerRating;
use App\Models\ExpertReferral;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\MyOperatorSMSService;
use App\Traits\AwsS3Trait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Validator;

class CustomerController extends BaseController
{
    use AwsS3Trait;
    protected $smsService;

    public function __construct(MyOperatorSMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function dashboard(Request $request)
    {
        $astrologer = User::role('astrologer')
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
        ];
        return $this->sendResponse($data, 'Dashboard retrieved successfully.');
    }

    public function registerProfile(Request $request)
    {
        $customer = User::where('id', $request->mobile_number)->first();
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile_code' => 'required',
            'mobile_number' => 'required|unique:users,mobile_number',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required',
            'city_id' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $name_parts = explode(" ", $request->full_name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        $mobileNumber = $request->input('mobile_number');
        $isProduction = env('APP_ENV') === 'production';
        $isStaging = env('APP_ENV') === 'staging';
        $settings = Setting::where('name', 'is_ios_review')->first();
        $isReview = $settings->data === 'true';
        $shouldSendOtp = $isProduction || ($isStaging && $isReview);
        $randomNumber = $shouldSendOtp ? rand(1000, 9999) : 1234;
        if ($shouldSendOtp) {
            if ($request->mobile_code === 91) {
                $message = "Your One-Time Password is {$randomNumber}. It will expire in 30 minutes. Please do not share this code with anyone. - SUBASTRO";
                $response = $this->smsService->sendSMS($mobileNumber, $message);
            } else {
                $subject = "Your One-Time Password (OTP)";
                $body = "Your OTP is {$randomNumber}. It will expire in 30 minutes. Please do not share this code with anyone. - SUBASTRO";
                Mail::to($request->email)->send(new \App\Mail\SendOtpMail($subject, $body));
            }
        }
        
        $customer = User::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $request->email,
            'otp' => $randomNumber,
            // 'otp' => 1234,
            'mobile_code' => $request->mobile_code,
            'mobile_number' => $request->mobile_number,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'city_id' => $request->city_id,
            'zego_user_id' => rand(1000,9999).time(),
            'referral_code' => $request->ref,
        ]);
        $data = [
            'token' =>  $customer->createToken('MyApp')->accessToken,
            'id' => $customer->id,
            'name' => $customer->full_name,
            'role' => $customer->getRoleNames()->first(),
            'email' => $customer->email,
            'mobile_code' => $customer->mobile_code,
            'mobile_number' => (int)$customer->mobile_number,
            'otp' => $customer->otp,
            'gender' => $customer->gender,
            'dob' => $customer->dob,
            'city_id' => $customer->city_id,
            'city_name' =>  optional($customer->city)->name,
            'zego_user_id' => $customer->zego_user_id,
            'device_token' => $customer->device_token,
            'profile_picture' => $customer->profile_picture,
            'device_name' => $customer->device_name,
            'referral_code' => $customer->referral_code,
        ];
        $customer->assignRole('customer');
        $referral = ExpertReferral::where('referral_code', $customer->referral_code)->first();
        if ($referral) {
            $referral->increment('download_count');
        }
        return $this->sendResponse($data, 'Profile registered successfully.');
    }

    public function updateProfile(Request $request)
    {
        $customer = User::where('id', auth('api')->user()->id)->first();
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required',
            'city_id' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $name_parts = explode(" ", $request->full_name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        $customer->update([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'city_id' => $request->city_id,
        ]);

        $data = [
            'id' => $customer->id,
            'name' => $customer->full_name,
            'role' => $customer->getRoleNames()->first(),
            'email' => $customer->email,
            'mobile_code' => $customer->mobile_code,
            'mobile_number' => (int)$customer->mobile_number,
            'otp' => $customer->otp,
            'gender' => $customer->gender,
            'dob' => $customer->dob,
            'city_id' => $customer->city_id,
            'city_name' =>  optional($customer->city)->name,
            'zego_user_id' => $customer->zego_user_id,
            'device_token' => $customer->device_token,
            'profile_picture' => $customer->profile_picture,
            'device_name' => $customer->device_name,
            'referral_code' => $customer->referral_code,
        ];
        return $this->sendResponse($data, 'Profile updated successfully.');
    }

    public function profilePicture(Request $request){
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }
        $customer = User::where('id', auth('api')->user()->id)->first();
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $uploadedUrl = $this->uploadFileToS3($file, 'customers/profile-picture', 'Customer');
            $customer->update(['profile_picture' => $uploadedUrl]);
            $data = [
                'profile_picture' => $customer->profile_picture,
            ];
            return $this->sendResponse($data, 'Profile picture changed successfully.');
        }
    }

    public function getProfile(Request $request)
    {
        $customer = User::where('id', auth('api')->user()->id)->first();
        $data = [
            'token' =>  $customer->createToken('MyApp')->accessToken,
            'id' => $customer->id,
            'name' => $customer->full_name,
            'role' => $customer->getRoleNames()->first(),
            'email' => $customer->email,
            'mobile_code' => $customer->mobile_code,
            'mobile_number' => (int)$customer->mobile_number,
            'otp' => $customer->otp,
            'gender' => $customer->gender,
            'dob' => $customer->dob,
            'city_id' => $customer->city_id,
            'city_name' =>  optional($customer->city)->name,
            'zego_user_id' => $customer->zego_user_id,
            'device_token' => $customer->device_token,
            'profile_picture' => $customer->profile_picture,
            'device_name' => $customer->device_name,
            'referral_code' => $customer->referral_code,
        ];
        return $this->sendResponse($data, 'Profile get successfully.');
    }

    public function getAstrologers(Request $request)
    {
        $query = User::role('astrologer')
            ->where('status', 1)
            ->with(['ratings' => function ($q) {
                $q->selectRaw('astrologer_id, AVG(ratings) as avg_rating')
                ->groupBy('astrologer_id');
            }]);
        
        if ($request->query('keywords')) {
            $keywords = $request->query('keywords');
            $query->where('keywords', 'LIKE', "%$keywords%");
        }

        if ($request->query('query')) {
            $name = $request->query('query');
            $query->where('first_name', 'LIKE', "$name%");
        }
    
        if ($request->query('top-experts') == 1) {
            $astrologers = $query->get();
        
            $topExperts = User::role('astrologer')
                ->where('status', 1)
                ->where('is_top_expert', 1)
                ->get();
        
            $astrologers = $astrologers->merge($topExperts)->unique('id');
        
            $onlineMales = $astrologers->where('is_online', 1)->where('gender', 'Male');
            $onlineFemales = $astrologers->where('is_online', 1)->where('gender', 'Female');
            $offlineMales = $astrologers->where('is_online', 0)->where('gender', 'Male');
            $offlineFemales = $astrologers->where('is_online', 0)->where('gender', 'Female');
        
            $topMale = $astrologers->where('gender', 'Male')->where('is_top_expert', 1)->first();
            $topFemale = $astrologers->where('gender', 'Female')->where('is_top_expert', 1)->first();
        
            if ($topMale) {
                $onlineMales = $onlineMales->reject(fn($a) => $a->id == $topMale->id);
                $offlineMales = $offlineMales->reject(fn($a) => $a->id == $topMale->id);
            }
        
            if ($topFemale) {
                $onlineFemales = $onlineFemales->reject(fn($a) => $a->id == $topFemale->id);
                $offlineFemales = $offlineFemales->reject(fn($a) => $a->id == $topFemale->id);
            }
        
            $onlineMales = $onlineMales->shuffle();
            $offlineMales = $offlineMales->shuffle();
            $onlineFemales = $onlineFemales->shuffle();
            $offlineFemales = $offlineFemales->shuffle();
        
            $maleList = collect();
            $femaleList = collect();
        
            if ($topMale) $maleList->push($topMale);
            $maleList = $maleList
                ->concat($onlineMales)
                ->concat($offlineMales)
                ->take(4);
        
            if ($topFemale) $femaleList->push($topFemale);
            $femaleList = $femaleList
                ->concat($onlineFemales)
                ->concat($offlineFemales)
                ->take(6);
        
            $filteredAstrologers = $maleList->concat($femaleList);
        } else {
            if ($request->query('recently-added') == 1) {
                $query->orderByDesc('created_at');
            }
            if ($request->query('is_online') == 1) {
                $query->where('is_online', 1);
            }
            
            $filteredAstrologers = $query->orderByDesc('is_online')
                                         ->orderBy('id', 'asc')
                                         ->paginate($request->per_page);
        }
        $data = $filteredAstrologers->map(function ($astrologer) {
            $astrologerAvgRating = AstrologerRating::where('astrologer_id', $astrologer->id)->avg('ratings') ?? 0;
            $astrologerTotalRatings = AstrologerRating::where('astrologer_id', $astrologer->id)->count();
            $appointmentIds = Appointment::where('astrologer_id', $astrologer->id)->pluck('id');

            $appointmentAvgRating = AppointmentRating::whereIn('appointment_id', $appointmentIds)->avg('ratings') ?? 0;
            $appointmentTotalRatings = AppointmentRating::whereIn('appointment_id', $appointmentIds)->count();
            $totalRatings = $astrologerTotalRatings + $appointmentTotalRatings;
            $combinedAvgRating = $totalRatings > 0 
                ? (($astrologerAvgRating * $astrologerTotalRatings) + ($appointmentAvgRating * $appointmentTotalRatings)) / $totalRatings 
                : 0;
            $settingPrices = $this->getAllPrices();
            $schedule = $astrologer->astrologerSchedule()->first();
            $bookNowPrice = $astrologer->bookNowPrices()->first();
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
            return[
                'id' => $astrologer->id,
                'name' => $astrologer->full_name,
                'professional_title' => str_replace(',', ', ', $astrologer->professional_title),
                'profile_picture' => $astrologer->profile_picture,
                'cut_out_image' => $astrologer->cut_out_image,
                'average_rating' => $combinedAvgRating >= 4.0 ? number_format($combinedAvgRating, 2) : '0.00',
                'total_ratings' => $totalRatings,
                'language' => str_replace(',', ', ', $astrologer->language),
                'experience' => $astrologer->experience,
                'keywords' => str_replace(',', ', ', $astrologer->keywords),
                'is_online' => $astrologer->is_online,
                'waiting_time_second' => $waitingTimeInSeconds,
                'voice_call_price_30min' => $schedule->audio_call_price_30min ?? $settingPrices['voice_30_min_price'],
                'voice_call_price_60min' => $schedule->audio_call_price_60min ?? $settingPrices['voice_60_min_price'],
                'video_call_price_30min' => $schedule->video_call_price_30min ?? $settingPrices['video_30_min_price'],
                'video_call_price_60min' => $schedule->video_call_price_60min ?? $settingPrices['video_60_min_price'],
                'chat_price' => $bookNowPrice->chat_price ?? $settingPrices['chat_min_price'],
                'voice_price' => $bookNowPrice->voice_price ?? $settingPrices['voice_min_price'],
                'video_price' => $bookNowPrice->video_price ?? $settingPrices['video_min_price'],
            ];
        });
        if($request->query('top-experts') == 1){
            return $this->sendResponse($data, 'Astologer retrived successfully.');
        } else{
            return $this->sendResponse($data, 'Astologer retrived successfully.', $filteredAstrologers);
        }
    }

    public function getOnlineAstrologers(Request $request)
    {
        $query = User::role('astrologer')
            ->where('status', 1)
            ->where('is_online', 1)
            ->with(['ratings' => function ($q) {
                $q->selectRaw('astrologer_id, AVG(ratings) as avg_rating')
                ->groupBy('astrologer_id');
            }]);
        if ($request->query('keywords')) {
            $keywords = $request->query('keywords');
            $query->where('keywords', 'LIKE', "%$keywords%");
        }

        if ($request->query('query')) {
            $name = $request->query('query');
            $query->where('first_name', 'LIKE', "$name%");
        }
        
        $astrologers = $query->orderByDesc('id')->paginate($request->per_page);
        $data = $astrologers->filter(function ($astrologer) {
            $bookNowPrice = $astrologer->bookNowPrices()->first();
            return $bookNowPrice && $bookNowPrice->available_credits > 0;
        })->map(function ($astrologer) {
            $astrologerAvgRating = AstrologerRating::where('astrologer_id', $astrologer->id)->avg('ratings') ?? 0;
            $astrologerTotalRatings = AstrologerRating::where('astrologer_id', $astrologer->id)->count();
            $appointmentIds = Appointment::where('astrologer_id', $astrologer->id)->pluck('id');

            $appointmentAvgRating = AppointmentRating::whereIn('appointment_id', $appointmentIds)->avg('ratings') ?? 0;
            $appointmentTotalRatings = AppointmentRating::whereIn('appointment_id', $appointmentIds)->count();
            $totalRatings = $astrologerTotalRatings + $appointmentTotalRatings;
            $combinedAvgRating = $totalRatings > 0 
                ? (($astrologerAvgRating * $astrologerTotalRatings) + ($appointmentAvgRating * $appointmentTotalRatings)) / $totalRatings 
                : 0;
            $settingPrices = $this->getAllPrices();
            $schedule = $astrologer->astrologerSchedule()->first();
            $bookNowPrice = $astrologer->bookNowPrices()->first();
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
            return[
                'id' => $astrologer->id,
                'name' => $astrologer->full_name,
                'professional_title' => str_replace(',', ', ', $astrologer->professional_title),
                'profile_picture' => $astrologer->profile_picture,
                'cut_out_image' => $astrologer->cut_out_image,
                'average_rating' => number_format($combinedAvgRating,2),
                'total_ratings' => $totalRatings,
                'language' => str_replace(',', ', ', $astrologer->language),
                'experience' => $astrologer->experience,
                'keywords' => str_replace(',', ', ', $astrologer->keywords),
                'is_online' => $astrologer->is_online,
                'waiting_time_second' => $waitingTimeInSeconds,
                'voice_call_price_30min' => $schedule->audio_call_price_30min ?? $settingPrices['voice_30_min_price'],
                'voice_call_price_60min' => $schedule->audio_call_price_60min ?? $settingPrices['voice_60_min_price'],
                'video_call_price_30min' => $schedule->video_call_price_30min ?? $settingPrices['video_30_min_price'],
                'video_call_price_60min' => $schedule->video_call_price_60min ?? $settingPrices['video_60_min_price'],
                'chat_price' => $bookNowPrice->chat_price ?? $settingPrices['chat_min_price'],
                'voice_price' => $bookNowPrice->voice_price ?? $settingPrices['voice_min_price'],
                'video_price' => $bookNowPrice->video_price ?? $settingPrices['video_min_price'],
                'available_credits' => $bookNowPrice->available_credits,
            ];
        })->values();
        return $this->sendResponse($data, 'Astologer retrived successfully.', $astrologers);
    }

    public function getAstrologerDetail($id)
    {
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
        ];
        return $this->sendResponse($data, 'Astologer detail retrived successfully.');
    }

    public function deleteAccount()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->sendError('Customer not found');
        }
        
        try {
            $user->delete();
            return $this->sendResponse([], 'Your account has been deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->sendError('Failed to delete account. Please try again.');
        }
    }

    public function getCities()
    {
        $cities = \DB::table('cities')
            ->join('states', 'cities.state_id', '=', 'states.id')
            ->join('countries', 'states.country_id', '=', 'countries.id')
            ->select('cities.*')
            ->orderBy('cities.name', 'asc')
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                ];
            });


        return $this->sendResponse($cities, 'Cities retrived successfully.');
    }
    public function registerProfileForNumero(Request $request)
    {
        $customer = User::where('id', $request->mobile_number)->first();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'email' => 'required',
            'mobile_code' => 'required',
            'mobile_number' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $existingUser = User::where('mobile_number', $request->mobile_number)->first();

        if ($existingUser) {
            return $this->sendResponse(['id' => $existingUser->id,'token' => $existingUser->createToken('MyApp')->accessToken], 'User already exists.');
        }

        $randomNumber = rand(1000,9999);
        
        $customer = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            // 'otp' => $randomNumber,
            'otp' => 1234,
            'mobile_code' => $request->mobile_code,
            'mobile_number' => $request->mobile_number,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'zego_user_id' => rand(1000,9999).time(),
        ]);
        $data = [
            'id' => $customer->id,
            'token' => $customer->createToken('MyApp')->accessToken,
        ];
        $customer->assignRole('customer');
        return $this->sendResponse($data, 'User registered successfully.');
    }
    public function getExpert()
    {
        $user = auth('api')->user(); 
        $showProducts = $user && $user->hasRole('customer');

        $astrologer = User::role('astrologer')
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