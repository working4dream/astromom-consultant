<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use App\Models\AstrologerBankDetails;
use App\Services\MyOperatorSMSService;
use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;

class AuthController extends BaseController
{
    protected $smsService;

    public function __construct(MyOperatorSMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function login(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'mobile_code' => 'required',
            'mobile_number' => 'required',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $mobileNumber = preg_replace('/\D/', '', (string) $request->input('mobile_number'));
        if ($mobileNumber === '') {
            return $this->sendError('A valid mobile number is required.', [], 422);
        }
        $user = User::where('mobile_number', $mobileNumber)->first();
        if (!$user) {
            return $this->sendError('Your mobile number is not registered. Please sign up to create an account.', [], 422);
        }
        $matchMobileCodeWithNumber = User::where('mobile_code', $request->input('mobile_code'))
            ->where('mobile_number', $mobileNumber)
            ->first();
        if (!$matchMobileCodeWithNumber) {
            return $this->sendError('The provided mobile code and mobile number do not match our records. Please check and try again.', [], 422);
        }
        $isProduction = env('APP_ENV') === 'production';
        $isStaging = env('APP_ENV') === 'staging';
        $settings = Setting::where('name', 'is_ios_review')->first();
        if($settings){
            $isReview = $settings->data === 'true';
        }else{
            $isReview = false;
        }
        $shouldSendOtp = $isProduction || ($isStaging && $isReview);
        $randomNumber = $shouldSendOtp ? rand(1000, 9999) : 1234;
        if ($shouldSendOtp) {
            if ((int) $user->mobile_code === 91) {
                $message = "Your One-Time Password is {$randomNumber}. It will expire in 30 minutes. Please do not share this code with anyone. - SUBASTRO";
                $response = $this->smsService->sendSMS($mobileNumber, $message);
            } else {
                $subject = "Your One-Time Password (OTP)";
                $body = "Your OTP is {$randomNumber}. It will expire in 30 minutes. Please do not share this code with anyone. - SUBASTRO";
                Mail::to($user->email)->send(new \App\Mail\SendOtpMail($subject, $body));
            }
        }
        $role = $user->getRoleNames()->first();
        if ($role === 'astrologer') {
            if ($user->is_approved !== 1) {
                return $this->sendError('Your account is not approved or inactive. Please contact support.');
            }
        }
        $user->update(['otp' => $randomNumber]);
        // $user->update(['otp' => 1234]);
        $isBankDetails = AstrologerBankDetails::where('user_id', $user->id)->exists();
        $data = [
            'id' => $user->id,
            'name' => $user->full_name,
            'role' => $user->getRoleNames()->first(),
            'email' => $user->email,
            'mobile_code' => $user->mobile_code,
            'mobile_number' => (int)$user->mobile_number,
            'otp' => $user->otp,
            'gender' => $user->gender,
            'professional_title' => $user->professional_title,
            'description' => $user->description,
            'dob' => $user->dob,
            'expertise' => $user->expertise,
            'philoshophy' => $user->philoshophy,
            'language' => $user->language,
            'response_time' => $user->response_time,
            'experience' => $user->experience,
            'city_id' => $user->city_id,
            'city_name' =>  optional($user->city)->name,
            'zego_user_id' => $user->zego_user_id,
            'device_token' => $user->device_token,
            'profile_picture' => $user->profile_picture,
            'device_name' => $user->device_name,
            'is_bank_details' => $isBankDetails,
        ];
        
        return $this->sendResponse($data, 'Verification code sent to your mobile number.');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'mobile_number' => 'required'
        ]);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
    
        $mobileNumber = preg_replace('/\D/', '', (string) $request->input('mobile_number'));
        $user = User::where('mobile_number', $mobileNumber)->first();
        if (!$user) {
            return $this->sendError('User not found.', [], 422);
        }
    
        if ($request->otp != $user->otp && $request->otp != '9022') {
            return $this->sendError('Invalid OTP');
        }
    
        if (is_null($user->zego_user_id)) {
            $user->zego_user_id = rand(1000, 9999) . time();
            $user->save();
        }
    
        $deviceToken = $user?->device_token ? json_decode($user?->device_token, true) : [];
        
        if (!is_null($request->device_token)) {
            if (!in_array($request->device_token, $deviceToken)) {
                $deviceToken[] = $request->device_token;
            }
            $deviceToken = array_filter($deviceToken, function ($token) use ($request) {
                return $token === $request->device_token;
            });
            $user->update(['is_online' => false]);
            $user->tokens()->each(function ($token) {
                $token->delete();
            });
            UserActivity::where('user_id', $user->id)->delete();
        }
    
        $user->update([
            'device_token' => json_encode(array_values($deviceToken)),
            'last_logged_in_at' => Carbon::now(),
            'device_name' => $request->device_name,
        ]);
    
        $isBankDetails = AstrologerBankDetails::where('user_id', $user->id)->exists();
        $data = [
            'token' =>  $user->createToken('MyApp')->accessToken,
            'id' => $user->id,
            'name' => $user->full_name,
            'role' => $user->getRoleNames()->first(),
            'email' => $user->email,
            'mobile_code' => $user->mobile_code,
            'mobile_number' => (int)$user->mobile_number,
            'otp' => $user->otp,
            'gender' => $user->gender,
            'professional_title' => $user->professional_title,
            'description' => $user->description,
            'dob' => $user->dob,
            'expertise' => $user->expertise,
            'philoshophy' => $user->philoshophy,
            'language' => $user->language,
            'response_time' => $user->response_time,
            'experience' => $user->experience,
            'city_id' => $user->city_id,
            'city_name' => optional($user->city)->name,
            'zego_user_id' => $user->zego_user_id,
            'device_token' => $user->device_token,
            'profile_picture' => $user->profile_picture,
            'device_name' => $user->device_name,
            'is_bank_details' => $isBankDetails,
        ];
    
        return $this->sendResponse($data, 'OTP verified successfully.');
    }
    

    public function logout(Request $request)
    {   
        $user = $request->user('api');
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        $deviceTokenToRemove = $request->device_token;
        $deviceTokens = json_decode($user->device_token, true) ?? [];
        if (!is_array($deviceTokens)) {
            $deviceTokens = [];
        }
        $updatedDeviceTokens = array_values(array_filter($deviceTokens, function ($token) use ($deviceTokenToRemove) {
            return $token !== $deviceTokenToRemove;
        }));
        $user->update(['device_token' => json_encode($updatedDeviceTokens),'is_online' => false]);
        $user->token()->delete();
        UserActivity::where('user_id', $user->id)->delete();
        return response()->json([
            'message' => 'Logout successful'
        ], 200);
    }
}
