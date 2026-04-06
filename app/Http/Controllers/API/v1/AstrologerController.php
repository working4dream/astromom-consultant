<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Models\CallLog;
use App\Models\Setting;
use App\Traits\AwsS3Trait;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ExpertReferral;
use App\Models\AstrologerEarning;
use App\Models\AstrologerSchedule;
use App\Models\AstrologerWithdrawal;
use App\Models\AstrologerBankDetails;
use App\Models\AstrologerBookNowPrice;
use App\Models\AstrologerWalletHistory;
use App\Http\Controllers\API\BaseController;

class AstrologerController extends BaseController
{
    use AwsS3Trait;
    public function dashboard()
    {
        $now = Carbon::now();
        $todayDate = $now->toDateString();
        $currentTime = $now->toTimeString();
        Appointment::where(function ($query) use ($todayDate, $currentTime) {
                $query->where('date', '<', $todayDate)
                    ->orWhere(function ($q) use ($todayDate, $currentTime) {
                        $q->where('date', '=', $todayDate)
                            ->where('end_time', '<', $currentTime);
                    });
            })
            ->where('booking_status', 15)
            ->update(['booking_status' => 17]);

        $astrologerId = auth('api')->user()->id;
        $earnings = AstrologerEarning::where('astrologer_id', $astrologerId)->where('status', 1);
        $appointments = Appointment::where('astrologer_id', $astrologerId);


        $upcomingAppointments = Appointment::with(['customer' => function ($query) {
            $query->withTrashed();
        }])
        ->where('astrologer_id', $astrologerId)
        ->where('booking_status', 15)
        ->orderBy('date')
        ->orderBy('start_time')
        ->take(5)
        ->get()
        ->map(function ($appointment) {
            $customer = $appointment->customer;
            return [
                'id' => $appointment->id,
                'name' => optional($customer)->full_name,
                'profile_picture' => optional($customer)->profile_picture,
                'zego_user_id' => optional($customer)->zego_user_id,
                'connect_type' => $appointment->connect_type,
                'service_type' => $appointment->service_type,
                'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                'time' => $appointment->time_period,
                'duration' => $appointment->duration,
                'booking_status_name' => Status::where('id', $appointment->booking_status)->value('name')
            ];
        });

        $recentEarnings = AstrologerEarning::where('astrologer_id', $astrologerId)
            ->where('status', 1)
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->map(function($earning){
                $appointment = optional($earning->appointment);
                $customer = optional($appointment->customer())?->withTrashed()?->first();
                $call_time = CallLog::where('appointment_id', $earning->appointment_id)->first()?->call_time;
                return [
                    'id' => $earning->id,
                    'name' => optional($customer)->full_name,
                    'date' => Carbon::parse($earning->created_at)->format('d-M-Y'),
                    'price' => $earning->amount,
                    'connect_type' => optional($earning->appointment)->connect_type,
                    'call_time' => $call_time,
                ];
            });
        $data = [
            "total_earning" => $earnings->sum('amount'),
            "total_appointments" => $appointments->count(),
            "upcoming_appointments" => $upcomingAppointments,
            "recent_earnings" => $recentEarnings,
        ];
        return $this->sendResponse($data, 'Dashboard retrieved successfully.');
    }

    public function updateProfile(Request $request)
    {
        $astrologer = User::where('id', auth('api')->user()->id)->first();
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $astrologer->id,
            // 'mobile_number' => 'required|unique:users,mobile_number,' . $astrologer->id,
            'gender' => 'required|in:Male,Female,Other',
            'professional_title' => 'required|string',
            'city_id' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $fullName = $request->full_name;
        $slug = Str::slug($fullName);

        $originalSlug = $slug;
        $i = 1;
        while (User::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $name_parts = explode(" ", $request->full_name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        $astrologer->update([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $request->email,
            'gender' => $request->gender,
            'professional_title' => $request->professional_title,
            'slug' => $slug,
            'description' => $request->description,
            'expertise' => $request->expertise,
            'keywords' => $request->keywords,
            'language' => $request->language,
            'experience' => $request->experience,
            'city_id' => $request->city_id,
        ]);
        $isBankDetails = AstrologerBankDetails::where('user_id', $astrologer->id)->exists();
        $data = [
            'id' => $astrologer->id,
            'name' => $astrologer->full_name,
            'role' => $astrologer->getRoleNames()->first(),
            'email' => $astrologer->email,
            'mobile_code' => $astrologer->mobile_code,
            'mobile_number' => (int)$astrologer->mobile_number,
            'otp' => $astrologer->otp,
            'gender' => $astrologer->gender,
            'professional_title' => $astrologer->professional_title,
            'description' => $astrologer->description,
            'expertise' => $astrologer->expertise,
            'keywords' => $astrologer->keywords,
            'language' => $this->languageShort($astrologer->language),
            'experience' => $astrologer->experience,
            'city_id' => $astrologer->city_id,
            'city_name' =>  optional($astrologer->city)->name,
            'zego_user_id' => $astrologer->zego_user_id,
            'device_token' => $astrologer->device_token,
            'profile_picture' => $astrologer->profile_picture,
            'is_bank_details' => $isBankDetails,
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
        $astrologer = User::where('id', auth('api')->user()->id)->first();
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $uploadedUrl = $this->uploadFileToS3($file, 'astrologers/profile-picture', 'Astrologer');
            $astrologer->update(['profile_picture' => $uploadedUrl]);
            $data = [
                'profile_picture' => $customer->profile_picture,
            ];
            return $this->sendResponse($data, 'Profile picture changed successfully.');
            
        }
    }

    public function getProfile(Request $request)
    {
        $astrologer = User::where('id', auth('api')->user()->id)->first();
        $isBankDetails = AstrologerBankDetails::where('user_id', $astrologer->id)->exists();
        $data = [
            'token' =>  $astrologer->createToken('MyApp')->accessToken,
            'id' => $astrologer->id,
            'name' => $astrologer->full_name,
            'role' => $astrologer->getRoleNames()->first(),
            'email' => $astrologer->email,
            'mobile_code' => $astrologer->mobile_code,
            'mobile_number' => (int)$astrologer->mobile_number,
            'otp' => $astrologer->otp,
            'gender' => $astrologer->gender,
            'professional_title' => $astrologer->professional_title,
            'description' => $astrologer->description,
            'expertise' => $astrologer->expertise,
            'keywords' => $astrologer->keywords,
            'language' => $this->languageShort($astrologer->language),
            'experience' => $astrologer->experience,
            'city_id' => $astrologer->city_id,
            'city_name' =>  optional($astrologer->city)->name,
            'zego_user_id' => $astrologer->zego_user_id,
            'device_token' => $astrologer->device_token,
            'profile_picture' => $astrologer->profile_picture,
            'is_bank_details' => $isBankDetails,
        ];
        return $this->sendResponse($data, 'Profile get successfully.');
    }

    public function schedule(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'future_days' => 'required',
        //     'schedule' => 'required',
        //     'not_available_days' => 'nullable|array',
        //     'is_availability' => 'required|boolean',
        // ]);
     
        // if($validator->fails()){
        //     return $this->sendError($validator->errors()->first());       
        // }

        $schedule = AstrologerSchedule::where('astrologer_id', auth('api')->user()->id)->first();

        if ($schedule) {
            $schedule->update([
                'future_days' => $request->future_days,
                'duration_minutes' => 0,
                'schedule' => json_encode($request->schedule),
                'not_available_days' => $request->not_available_days,
                'is_availability' => $request->is_availability,
            ]);

            return $this->sendResponse($schedule, 'Schedule updated successfully.');
        } else {
            $schedule = AstrologerSchedule::create([
                'astrologer_id' => auth('api')->user()->id,
                'future_days' => $request->future_days,
                'duration_minutes' => 0,
                'schedule' => json_encode($request->schedule),
                'not_available_days' => $request->not_available_days,
                'is_availability' => $request->is_availability,
            ]);

            return $this->sendResponse($schedule, 'Schedule created successfully.');
        }

    }

    public function getSchedule()
    {
        $schedule = AstrologerSchedule::where('astrologer_id',auth('api')->user()->id)->first();
        if (!$schedule) {
            return $this->sendResponse([], 'Schedule not found.');
        }
        $bookNow = AstrologerBookNowPrice::where('astrologer_id',auth('api')->user()->id)->first();
        $scheduleData = json_decode($schedule->schedule, true);
        $scheduleData = !empty($scheduleData) ? $scheduleData : [];
        $settingPrices = $this->getAllPrices();
        $data = [
            'id' => $schedule->id,
            'astrologer_id' => auth('api')->user()->id,
            'future_days' => $schedule->future_days,
            'schedule' => $scheduleData,
            'is_availability' => $schedule->is_availability,
            'video_call_price_30min' => $schedule->video_call_price_30min ?? $settingPrices['video_30_min_price'],
            'video_call_price_60min' => $schedule->video_call_price_60min ?? $settingPrices['video_60_min_price'],
            'voice_call_price_30min' => $schedule->audio_call_price_30min ?? $settingPrices['voice_30_min_price'],
            'voice_call_price_60min' => $schedule->audio_call_price_60min ?? $settingPrices['voice_60_min_price'],
            'chat_price' => $bookNow->chat_price ?? $settingPrices['chat_min_price'],
            'voice_price' => $bookNow->voice_price ?? $settingPrices['voice_min_price'],
            'video_price' => $bookNow->video_price ?? $settingPrices['video_min_price'],
        ];
        return $this->sendResponse($data, 'Schedule retrived successfully.');
    }

    public function deleteAccount()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->sendError('Astrologer not found');
        }

        try {
            $user->delete();
            return $this->sendResponse([], 'Your account has been deleted successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->sendError('Failed to delete account. Please try again.');
        }
    }

    public function getEarnings(Request $request)
    {
        $query = AstrologerEarning::where('astrologer_id', auth('api')->user()->id)
                    ->where('status', 1);

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }
    
        if ($request->filled('type')) {
            $query->whereHas('appointment', function ($q) use ($request) {
                $q->where('connect_type', $request->type)
                  ->where('booking_status', 17);
            });
        } else {
            $query->whereHas('appointment', function ($q) {
                $q->where('booking_status', 17);
            });
        }
    
        if ($request->filled('min') && $request->filled('max')) {
            $query->whereBetween('amount', [$request->min, $request->max]);
        }
    
        $earnings = $query->orderBy('id', 'DESC')->paginate($request->per_page);

        $earningData = $earnings->groupBy(function ($earning) {
            $date = $earning->created_at;
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
                'data' => $group->map(function ($earning) {
                    $customer = $earning->appointment->customer()->withTrashed()->first();
                    $call_time = CallLog::where('appointment_id', $earning->appointment_id)->first()?->call_time;
                    return [
                        'id' => $earning->id,
                        'name' => $customer?->full_name ?? 'N/A',
                        'date' => Carbon::parse($earning->created_at)->format('d-M-Y'),
                        'price' => $earning->amount,
                        'connect_type' => $earning->appointment->connect_type,
                        'call_time' => $call_time,
                    ];
                }),
            ];
        })->values();

        $totalEarning = AstrologerEarning::where('astrologer_id', auth('api')->user()->id)
                            ->where('status', 1)
                            ->sum('amount');
        $data = [
            'total_earnings' => doubleval($totalEarning),
            'data' => $earningData,
        ];
        return $this->sendResponse($data, 'Earning retrived successfully!', $earnings);
    }

    public function withdrawalRequest(Request $request)
    {
        $minAmount = env("MIN_WITHDRAW_AMOUNT");

        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric', 'min:' . $minAmount],
        ], [
            'amount.min' => "The amount must be greater than or equal to {$minAmount}.",
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }
    
        $astrologer = auth('api')->user();

        $existingRequest = AstrologerWithdrawal::where('astrologer_id', $astrologer->id)
            ->where('status', 27)
            ->exists();

        if ($existingRequest) {
            return $this->sendError('You already have a pending withdrawal request.');
        }

        $totalEarnings = AstrologerEarning::where('astrologer_id', $astrologer->id)->sum('amount');
    
        if ($request->amount > $totalEarnings) {
            return $this->sendError('Insufficient balance for withdrawal.');
        }

        $withdrawal = AstrologerWithdrawal::create([
            'astrologer_id' => $astrologer->id,
            'amount' => $request->amount,
        ]);

        return $this->sendResponse($withdrawal, 'Withdrawal request sent successfully');
    }

    public function getWithdrawalHistory(Request $request)
    {
        $astrologer = auth('api')->user();
        $query = AstrologerWithdrawal::where('astrologer_id', $astrologer->id);

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to = Carbon::parse($request->to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }

        $histories = $query->orderBy('id', 'DESC')->paginate($request->per_page);

        $historyData = $histories->groupBy(function ($history) {
            $date = $history->created_at;
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
                'data' => $group->map(function ($history) {
                    $status = Status::find($history->status)->name;
                    return [
                        'id' => $history->id,
                        'amount' => $history->amount,
                        'name' => Carbon::parse($history->created_at)->format('d-M-Y'),
                        'date' => "#" . $history->id,
                        'status' => $status,
                        'reject_reason' => $history->reject_reason,
                    ];
                }),
            ];
        })->values();

        $currentBalance = $this->getCurrentBalance();

        $data = [
            'current_balance' => $currentBalance,
            'data' => $historyData,
        ];

        return $this->sendResponse($data, 'Withdrawal history retrieved successfully', $histories);

    }
    
    public function getWallet(Request $request)
    {
        $astrologer = auth('api')->user();
        $histories = AstrologerWalletHistory::where('astrologer_id', $astrologer->id)
                        ->orderBy('id', 'DESC')
                        ->paginate($request->per_page);

        $historyData = $histories->groupBy(function ($history) {
            $date = $history->created_at;
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
                'data' => $group->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'amount' => $history->amount,
                        'message' => $history->message,
                        'type' => $history->type,
                    ];
                }),
            ];
        })->values();

        $currentBalance = $this->getCurrentBalance();

        $data = [
            'current_balance' => $currentBalance,
            'data' => $historyData,
        ];

        return $this->sendResponse($data, 'Withdrawal history retrived successfully', $histories);
    }

    public function getCurrentBalance()
    {
        $astrologer = auth('api')->user();
        $totalEarnings = AstrologerEarning::where('astrologer_id', $astrologer->id)->where('status', 1)->sum('amount');
        $totalWithdrawals = AstrologerWithdrawal::where('astrologer_id', $astrologer->id)->where('status', 28)->sum('amount');
        
        $currentBalance = $totalEarnings - $totalWithdrawals;

        return $currentBalance;
    }

    public function bookNowPrices(Request $request)
    {
        $settingPrices = $this->getAllPrices();
        $validator = Validator::make($request->all(), [
            'chat_price' => 'required|numeric|min:' . ($settingPrices['chat_min_price'] ?? 15) . '|max:' . ($settingPrices['chat_max_price'] ?? 30),
            'voice_price' => 'required|numeric|min:' . ($settingPrices['voice_min_price'] ?? 25) . '|max:' . ($settingPrices['voice_max_price'] ?? 40),
            'video_price' => 'required|numeric|min:' . ($settingPrices['video_min_price'] ?? 30) . '|max:' . ($settingPrices['video_max_price'] ?? 60),
            'audio_call_price_30min' => 'required|numeric|min:' . ($settingPrices['voice_30_min_price'] ?? 999) . '|max:' . ($settingPrices['voice_30_max_price'] ?? 1999),
            'audio_call_price_60min' => 'required|numeric|min:' . ($settingPrices['voice_60_min_price'] ?? 1999) . '|max:' . ($settingPrices['voice_60_max_price'] ?? 2999),
            'video_call_price_30min' => 'required|numeric|min:' . ($settingPrices['video_30_min_price'] ?? 1999) . '|max:' . ($settingPrices['video_30_max_price'] ?? 2999),
            'video_call_price_60min' => 'required|numeric|min:' . ($settingPrices['video_60_min_price'] ?? 2999) . '|max:' . ($settingPrices['video_60_max_price'] ?? 3999),
        ]);        

        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $prices = AstrologerBookNowPrice::updateOrCreate(
            ['astrologer_id' => auth('api')->user()->id],
            [
                'chat_price' => $request->chat_price,
                'voice_price' => $request->voice_price,
                'video_price' => $request->video_price,
            ]
        );
        
        $schedule = AstrologerSchedule::updateOrCreate(
            ['astrologer_id' => auth('api')->user()->id],
            [
                'video_call_price_30min' => $request->video_call_price_30min,
                'video_call_price_60min' => $request->video_call_price_60min,
                'audio_call_price_30min' => $request->audio_call_price_30min,
                'audio_call_price_60min' => $request->audio_call_price_60min,
            ]
        );

        $callPrices = AstrologerSchedule::where('astrologer_id', auth('api')->user()->id)->first([
            'video_call_price_30min',
            'video_call_price_60min',
            'audio_call_price_30min',
            'audio_call_price_60min',
        ]);
        
        return $this->sendResponse([
            'prices' => $prices,
            'schedule' => $callPrices
        ], 'Prices and schedule updated successfully.');
    }
    public function getExpertIn()
    {
        $expertIn = Setting::where('name', 'specialization')->first()->data;
        $expertList = explode(',', $expertIn);

        return $this->sendResponse($expertList, 'Expert In rerived successfully.');

    }
    public function getKeywords()
    {
        $keywords = Setting::where('name', 'keywords')->first()->data;
        $keywordsList = explode(',', $keywords);

        return $this->sendResponse($keywordsList, 'Keyword rerived successfully.');
    }
    public function getExpertises()
    {
        $expertises = Setting::where('name', 'expertise')->first()->data;
        $expertiseList = explode(',', $expertises);

        return $this->sendResponse($expertiseList, 'Expertise rerived successfully.');
    }
    public function getLanguages()
    {
        $language = Setting::where('name', 'languages')->first()->data;
        $languageList = explode(',', $language);

        return $this->sendResponse($languageList, 'Language rerived successfully.');
    }
    public function isOnline(Request $request)
    {
        $user = auth('api')->user();
        $status = $request->is_online ? 1 : 0;
        $schedule = User::where('id',$user->id);
        $schedule->update(['is_online'=> $request->is_online]);
        $data = [
            'is_online' => $schedule->first()->is_online,
        ];
        return $this->sendResponse($data, 'Changed');
    }

    public function storeBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'beneficiary_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'name' => 'required',
            'pan_number' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }
        $user = auth('api')->user();
        $existingData = AstrologerBankDetails::where('user_id', $user->id)->first();

        $data = AstrologerBankDetails::updateOrCreate(['id' => $existingData ? $existingData->id : null], [
            'user_id' => $user->id,
            'beneficiary_name' => $request->beneficiary_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'name' => $request->name,
            'pan_number' => $request->pan_number,
        ]);
        return $this->sendResponse($data, 'Bank details saved successfully');
    }

    public function getBankDetails()
    {
        $user = auth('api')->user();
        $bankDetails = AstrologerBankDetails::where('user_id', $user->id)->first();
        if (!$bankDetails) {
            return $this->sendError('Data not found');
        }
        $data = [
            'id' => $bankDetails->id,
            'user_id' => $bankDetails->user_id,
            'beneficiary_name' => $bankDetails->beneficiary_name,
            'account_number' => $bankDetails->account_number,
            'ifsc_code' => $bankDetails->ifsc_code,
            'name' => $bankDetails->name,
            'pan_number' => $bankDetails->pan_number,
        ];
        return $this->sendResponse($data, 'Bank details retrived successfully');
    }
    public function getReportPrices()
    {
        $data = Setting::whereIn('name', [
            'name_correction',
            'name_correction_exclusive',
            'relationship_comparability',
        ])->pluck('data', 'name')->map(function ($value) {
            return number_format((float)$value, 2, '.', '');
        });
        return $this->sendResponse($data, 'Prices Retrived successfully');
    }

    public function generateShareLink()
    {
        $astrologerId = auth('api')->user()->id;

        $existing = ExpertReferral::where('astrologer_id', $astrologerId)->first();

        if ($existing) {
            $data = [
                'id' => $existing->id,
                'astrologer_id' => $existing->astrologer_id,
                'share_link' => url('/expertInfoScreen?ref=' . $existing->referral_code.'&id='. $existing->astrologer_id),
            ];
            return $this->sendResponse($data, 'Referral link generated successfully');
        }

        $generate = ExpertReferral::create([
            'astrologer_id' => $astrologerId,
            'referral_code' => strtoupper(Str::random(8)),
        ]);

        $data = [
            'id' => $generate->id,
            'astrologer_id' => $generate->astrologer_id,
            'share_link' => url('/expertInfoScreen?ref=' . $generate->referral_code.'&id='. $generate->astrologer_id),
        ];

        return $this->sendResponse($data, 'Referral link generated successfully');
    }
}
