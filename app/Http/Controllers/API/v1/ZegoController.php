<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Models\CallLog;
use App\Models\Message;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AstrologerEarning;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\API\BaseController;

class ZegoController extends BaseController
{
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required',
            'appointment_id' => 'required',
            'sender_id' => 'required',
            'receiver_id' => 'required',
            'message' => 'required_if:message_types,text',
            'message_types' => 'required|in:text,image',
            'image_path' => 'required_if:message_types,image',
        ]);
     
        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $imagePath = null;

        if ($request->image_path) {
            $base64String = $request->image_path;

            $base64String = str_replace(' ', '+', $base64String);
            $imageData = base64_decode($base64String);
            if ($imageData !== false) {

                $finfo = finfo_open();
                $mimeType = finfo_buffer($finfo, $imageData, FILEINFO_MIME_TYPE);
                finfo_close($finfo);

                $extensions = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];

                if (isset($extensions[$mimeType])) {
                    $extension = $extensions[$mimeType];
                    $fileName = 'chat/images/' . $request->sender_id . '/' . uniqid() . '.' . $extension;
                    Storage::disk('s3')->put($fileName, $imageData);
                    $imagePath = $fileName;
                }
            }
        }

        $messageData = Message::create([
            'session_id'     => $request->session_id,
            'appointment_id' => $request->appointment_id,
            'sender_id'      => $request->sender_id,
            'receiver_id'    => $request->receiver_id,
            'message'        => $request->message,
            'message_types'  => $request->message_types,
            'image_path'     => $imagePath,
        ]);

        $messageData->status_name = Status::where('id', 21)->first()->name;

        // Send Notification
        if($request->is_chat_screen === 0)
        {
            $receiverUser = User::where('zego_user_id',$request->receiver_id)->first();
            $senderUser = User::where('zego_user_id',$request->sender_id)->first();
            $appointment = Appointment::where('id', $request->appointment_id)->first();
            $deviceTokens = json_decode($receiverUser->device_token);
            $title = "New message arrived from ". $senderUser->full_name;
            $message = "You have an active chat session. Tap to reply.";
            $extraData = [
                'title' => $title,
                'body' => $message,
                'type' => 'chat_message',
                'id' => $appointment->id,
                'name' => $senderUser->first_name . ' ' . $senderUser->last_name,
                'profile_picture' => $senderUser->profile_picture,
                'zego_user_id' => $senderUser->zego_user_id,
                'connect_type' => $appointment->connect_type,
                'date' => Carbon::parse($appointment->date)->format('d-M-Y'),
                'time' => $appointment->time_period,
                'duration' => $appointment->duration,
                'booking_status_name' => Status::find($appointment->booking_status)->name
            ];
            if (!empty($deviceTokens)) {
                $this->sendNotification($title, $message, $deviceTokens, $extraData);
            }
        }

        return $this->sendResponse($messageData, 'Message sent successfully');
    }

    public function callLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'call_id' => 'required',
            'appointment_id' => 'required',
            'status' => 'required',
        ]);
    
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
    
        $appointment = Appointment::where('id', $request->appointment_id)->first();
        if (!$appointment) {
            return $this->sendError('Appointment not found.');
        }
    
        $callLog = CallLog::where('appointment_id', $request->appointment_id)->first();
    
        if ($request->status == 24) {
            if (!$callLog) {
                $callLog = CallLog::create([
                    'appointment_id' => $request->appointment_id,
                    'call_id' => $request->call_id,
                    'caller_id' => $appointment->astrologer_id,
                    'receiver_id' => $appointment->customer_id,
                    'status' => $request->status,
                    'started_at' => now()->format('H:i:s'),
                    'date' => now(),
                    'duration' => 0,
                    'session_start_time' => $appointment->connect_type !== 'chat' ? now()->format('H:i:s') : null,
                ]);
            } else {
                $callLog->update([
                    'status' => $request->status,
                    'session_start_time' => $appointment->connect_type !== 'chat' ? now()->format('H:i:s') : null,
                ]);
            }
        }
    
        if ($request->status == 25 && $callLog) {
            if ($appointment->connect_type === 'chat') {
                $callLog->update([
                    'status' => $request->status,
                    'ended_at' => now()->format('H:i:s')
                ]);
                $appointment->update(['booking_status' => 17]);
                $startTime = Carbon::parse($callLog->started_at);
                $endTime = Carbon::parse($callLog->ended_at);

                $totalSeconds = $startTime->diffInSeconds($endTime);
                $minutes = floor($totalSeconds / 60);
                $seconds = $totalSeconds % 60;

                $durationFormatted = sprintf('%02d:%02d', $minutes, $seconds);

                $callLog->update(['call_time' => $durationFormatted]);
                $ref = $this->createFirebaseDatabase()->getReference('orderCount/'.$appointment->astrologer->id);
                $existingData = $ref->getValue();
                if ($existingData) {
                    $newCount = isset($existingData['count']) ? $existingData['count'] - 1 : 1;
                    $ref->update(['count' => $newCount]);
                }
            }
            if ($callLog->session_start_time) {
                $sessionStartTime = Carbon::parse($callLog->session_start_time);
                $endTime = now();
                $sessionDuration = $sessionStartTime->diffInSeconds($endTime);
        
                $previousCallTime = $callLog->duration ?? 0;
                $totalDuration = $previousCallTime + $sessionDuration;
        
                $minutes = floor($totalDuration / 60);
                $seconds = $totalDuration % 60;
                $formattedTime = sprintf('%02d:%02d', $minutes, $seconds);
        
                $callLog->update([
                    'status' => $request->status,
                    'ended_at' => now()->format('H:i:s'),
                    'duration' => $totalDuration,
                    'call_time' => $formattedTime,
                    'session_start_time' => null,
                ]);
            }
            AstrologerEarning::where('appointment_id',$appointment->id)->update(['status' => 1]);
            if ($callLog->ended_at && $callLog->ended_at < $appointment->end_time) {
                $nextAppointment = Appointment::where('astrologer_id', $appointment->astrologer_id)
                    ->where('id', '>', $appointment->id)
                    ->where('is_waiting', 1)
                    ->first();
    
                if ($nextAppointment) {
                    $originalStartTime = Carbon::parse($nextAppointment->start_time);
                    $originalEndTime = Carbon::parse($nextAppointment->end_time);
                    $duration = $originalStartTime->diffInMinutes($originalEndTime);
    
                    $nextAppointmentStartTime = Carbon::parse($callLog->ended_at)->addMinute()->second(0);
                    $nextAppointmentEndTime = $nextAppointmentStartTime->copy()->addMinutes($duration)->second(0);
    
                    $nextAppointment->update([
                        'start_time' => $nextAppointmentStartTime->format('H:i:s'),
                        'end_time' => $nextAppointmentEndTime->format('H:i:s'),
                        'time_period' => $nextAppointmentStartTime->format('H:i:s') . '-' . $nextAppointmentEndTime->format('H:i:s'),
                        'is_waiting' => 0,
                    ]);
                }
            }
        }
    
        if ($callLog) {
            $callLog->status_name = Status::where('id', $request->status)
                ->where('type', 'call_logs')
                ->first()?->name;
        }
    
        return $this->sendResponse($callLog, 'Call log updated successfully');
    }    

    public function getCallLog(Request $request)
    {
        $allowedType = $request->type;
        $searchName = $request->name ?? null;
        $userType = auth('api')->user()->getRoleNames()->first();
        
        $callLogs = CallLog::with(['appointment', $userType === 'astrologer' ? 'customer' : 'astrologer'])
            ->when($userType === 'astrologer', function ($query) {
                $query->where('caller_id', auth('api')->user()->id);
            })
            ->when($userType === 'customer', function ($query) {
                $query->where('receiver_id', auth('api')->user()->id);
            })
            ->when($allowedType, function ($query, $allowedType) {
                $query->whereHas('appointment', function ($q) use ($allowedType) {
                    $q->where('connect_type', $allowedType);
                });
            })
            ->when($searchName, function ($query) use ($searchName, $userType) {
                $relation = $userType === 'astrologer' ? 'customer' : 'astrologer';
                
                $query->whereHas($relation, function ($q) use ($searchName) {
                    $q->where(function ($subQuery) use ($searchName) {
                        $subQuery->where('first_name', 'like', "%{$searchName}%")
                                 ->orWhere('last_name', 'like', "%{$searchName}%")
                                 ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchName}%"]);
                    });
                });
            })
            ->whereHas('appointment', function ($query) {
                $query->whereIn('connect_type', ['video', 'voice']);
            })
            ->orderByDesc('id')
            ->paginate($request->per_page);
        
        $data = $callLogs->map(function ($callLog) use ($userType) {
            $appointment = $callLog->appointment;
            $user = $userType === 'astrologer' 
                ? $callLog->customer()->withTrashed()->first()
                : $callLog->astrologer()->withTrashed()->first();
        
            return [
                'id' => $callLog->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'profile_picture' => $user->profile_picture,
                'date' => Carbon::parse($callLog->date)->format('d M Y'),
                'type' => $appointment->connect_type,
                'call_time' => $callLog->call_time,
            ];
        });
        
        return $this->sendResponse($data->toArray(), 'Call log retrieved successfully.', $callLogs);
        
    }

    public function getZegoProfilePicture(Request $request)
    {
        $user = User::where('zego_user_id', $request->id)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->hasRole('astrologer')) {
            $image = $user->cut_out_image;
        } elseif ($user->hasRole('customer')) {
            $image = $user->profile_picture;
        } else {
            $image = null;
        }

        if (!$image) {
            return redirect("https://ui-avatars.com/api/?name=" . urlencode($user->full_name) . "&background=692E36&color=fff&length=1");
        }

        return redirect($image);

    }

    public function getIsEndedChat(Request $request)
    {
        $appointment_id = $request->appointment_id;
        $appointment = Appointment::where('id', $appointment_id)->where('booking_status', 17)->exists();
        $data = [
            'is_ended' => $appointment,
        ];
        return $this->sendResponse($data, 'Is ended chat retrived successfully.');
    }

    public function sendAlert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }
        
        $userType = auth('api')->user()->getRoleNames()->first();
        $appointment = Appointment::where('id', $request->appointment_id)->first();
        
        if (!$appointment) {
            return $this->sendError('Appointment not found.');
        }
        
        $user = null;
        $title = "";
        $message = "";
        $currentUser = auth('api')->user();
        $currentUserName = $currentUser->first_name . ' ' . $currentUser->last_name;
        
        if ($userType === 'customer') {
            $user = User::where('id', $appointment->astrologer_id)->first();
        } else {
            $user = User::where('id', $appointment->customer_id)->first();
        }
        
        if (!$user) {
            return $this->sendError('User not found.');
        }
        
        $title = "{$currentUserName} is waiting for Your Response";
        $message = "{$currentUserName} is waiting for you in the ongoing appointment. Please join now.";
        
        $deviceTokens = json_decode($user->device_token);
        
        if (!$deviceTokens) {
            return $this->sendError('No valid device tokens found.');
        }
        
        $extraData = [
            'title' => $title,
            'body' => $message,
            'type' => 'alert',
        ];
        
        $this->sendNotification($title, $message, $deviceTokens, $extraData, 'reminder_channel', 'alert_sound');    
        return $this->sendResponse($extraData, 'Alert sent successfully.');  
    }

    public function lastChatSession(Request $request)
    {
        $user = User::where('zego_user_id', $request->zego_user_id)->first();
        if (!$user) {
            return $this->sendError('User not found.', [], 404);
        }

        $astrologerId = null;
        $customerId = null;

        if ($user->hasRole('astrologer')) {
            $astrologerId = $user->id;
            $appointment = Appointment::where('astrologer_id', $astrologerId)
                ->where('customer_id', auth('api')->user()->id)
                ->where('connect_type', 'chat')
                ->where('booking_status', 17)
                ->latest()
                ->first();
            if ($appointment) {
                $callLog = CallLog::where('appointment_id', $appointment->id)->where('status',25)->latest()->first();
                if($callLog && $callLog->ended_at){
                    $date = \Carbon\Carbon::parse($callLog?->date);
                    $formattedDate = $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('d M Y'));
            
                    $time = \Carbon\Carbon::parse($callLog?->ended_at)->format('h:i A');
                    return $this->sendResponse(
                        (object) [], 
                        "Your last session ended on {$formattedDate}, {$time}. Resume your conversation seamlessly with new booking!"
                    );
                }else {
                    $date = \Carbon\Carbon::parse($appointment->date);
                    $formattedDate = $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('d M Y'));
            
                    $time = \Carbon\Carbon::parse($appointment->end_time)->format('h:i A');
                    return $this->sendResponse(
                        (object) [], 
                        "Your last session ended on {$formattedDate}, {$time}. Resume your conversation seamlessly with new booking!"
                    );
                }
            }
            else {
                return $this->sendError('Appointment not found.', [], 404);
            }

        } elseif ($user->hasRole('customer')) {
            $customerId = $user->id;
            $appointment = Appointment::where('astrologer_id', auth('api')->user()->id)
                ->where('customer_id', $customerId)
                ->where('connect_type', 'chat')
                ->where('booking_status', 17)
                ->latest()
                ->first();
            if ($appointment) {
                $callLog = CallLog::where('appointment_id', $appointment->id)->where('status',25)->latest()->first();
                if($callLog?->ended_at){
                    $date = \Carbon\Carbon::parse($callLog?->date);
                    $formattedDate = $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('d M Y'));
            
                    $time = \Carbon\Carbon::parse($callLog?->ended_at)->format('h:i A');
                    $customerName = $appointment->customer->full_name;
                    return $this->sendResponse(
                        (object) [], 
                        "{$customerName} ended last session on {$formattedDate}, {$time}."
                    );
                }else {
                    $date = \Carbon\Carbon::parse($appointment->date);
                    $formattedDate = $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('d M Y'));
            
                    $time = \Carbon\Carbon::parse($appointment->end_time)->format('h:i A');
                    return $this->sendResponse(
                        (object) [], 
                        "Your last session ended on {$formattedDate}, {$time}."
                    );
                }
            }
            else {
                return $this->sendError('Appointment not found.', [], 404);
            }
        }
        return $this->sendResponse((object) [], "No previous session found.");
    }

}
