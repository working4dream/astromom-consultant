<?php

namespace App\Http\Controllers\API\v1;

use Validator;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Status;
use App\Models\Message;
use App\Traits\AwsS3Trait;
use App\Models\Appointment;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Jobs\SendWebSocketUpdate;
use App\Http\Controllers\API\BaseController;
use App\Jobs\SendMessageJob;

class ChatController extends BaseController
{
    use AwsS3Trait;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,zego_user_id',
            'message' => 'required_if:message_types,text',
            'message_types' => 'required|in:text,image,audio,video',
            'image_path' => 'required_if:message_types,image|file|mimes:jpg,jpeg,png,webp|max:10240', 
            'audio_path' => 'required_if:message_types,audio|file|max:102400',
            'video_path' => 'required_if:message_types,video|file|mimes:mp4,mov,avi|max:102400',
            'reply_to_id' => 'nullable|exists:messages,id',
        ], [
            'image_path.max' => 'Image size should not exceed 10 MB.',
            'audio_path.max' => 'Audio size should not exceed 100 MB.',
            'video_path.max' => 'Video size should not exceed 100 MB.',
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors()->first());       
        }

        $imagePath = $request->hasFile('image_path') ? $this->uploadFileToS3($request->file('image_path'), 'chat/images') : null;
        $audioPath = $request->hasFile('audio_path') ? $this->uploadFileToS3($request->file('audio_path'), 'chat/audios') : null;
        $videoPath = $request->hasFile('video_path') ? $this->uploadFileToS3($request->file('video_path'), 'chat/videos') : null;

        $sender = auth('api')->user();
        $senderId = (int) $sender->zego_user_id;
        $receiverId = (int) $request->receiver_id;

        $userOne = min($senderId, $receiverId);
        $userTwo = max($senderId, $receiverId);

        $conversation = Conversation::firstOrCreate(
            ['user_one_id' => $userOne, 'user_two_id' => $userTwo]
        );

        $messageData = Message::create([
            'session_id'      => 0,
            'sender_id'      => $senderId,
            'receiver_id'    => $receiverId,
            'message'        => $request->message,
            'message_types'  => $request->message_types,
            'image_path'     => $imagePath,
            'audio_path'     => $audioPath,
            'video_path'     => $videoPath,
            'reply_to_id'    => (int)$request->reply_to_id,
            'conversation_id'=> $conversation->id,
        ]);
        $conversation->update([
            'last_message_id' => $messageData->id,
        ]);
        dispatch(new SendWebSocketUpdate(
            $request->receiver_id,
            'new_message',
            ['message' => $messageData]
        ));
        $data = [
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'message_types' => $request->message_types,
            'reply_to_id' => $request->reply_to_id,
            'appointment_id' => $request->appointment_id ?? null,
            'is_chat_screen' => $request->is_chat_screen ?? 0,
        ];
        SendMessageJob::dispatch($data, $sender);
        return $this->sendResponse($messageData, 'Message sent successfully');
    }

    public function index(Request $request, $userId)
    {
        $authUser = auth('api')->user();
        $authZegoId = $authUser->zego_user_id;
    
        $otherUser = User::where('zego_user_id', $userId)->first();
    
        if (!$otherUser) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $otherZegoId = $otherUser->zego_user_id;
    
        $perPage = (int)$request->get('per_page', 20);
    
        $messages = Message::with('replyTo')
            ->where(function ($q) use ($authZegoId, $otherZegoId) {
                $q->where(function ($q2) use ($authZegoId, $otherZegoId) {
                    $q2->where('sender_id', $authZegoId)
                    ->where('receiver_id', $otherZegoId);
                })
                ->orWhere(function ($q3) use ($authZegoId, $otherZegoId) {
                    $q3->where('sender_id', $otherZegoId)
                    ->where('receiver_id', $authZegoId);
                });
            })
            ->orderBy('id', 'desc')
            ->cursorPaginate($perPage, ['*'], 'cursor');

        Message::where('sender_id', $otherZegoId)
        ->where('receiver_id', $authZegoId)
        ->where('is_read', false)
        ->update(['is_read' => true]);
    
        $data = $messages->getCollection()->reverse()->values()->map(function ($message) {
            return [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'message' => $message->message,
                'message_types' => $message->message_types,
                'image_path' => $message->image_path,
                'audio_path' => $message->audio_path,
                'video_path' => $message->video_path,
                'is_read' => $message->is_read,
                'reply_to_id' => $message->reply_to_id,
                'timestamp' => $message->created_at,
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'sender_id' => $message->replyTo->sender_id,
                    'receiver_id' => $message->replyTo->receiver_id,
                    'message' => $message->replyTo->message,
                    'message_types' => $message->replyTo->message_types,
                    'image_path' => $message->replyTo->image_path,
                    'audio_path' => $message->replyTo->audio_path,
                    'video_path' => $message->replyTo->video_path,
                ] : null,
            ];
        });
        $totalMessagesCount = Message::where(function ($q) use ($authZegoId, $otherZegoId) {
            $q->where('sender_id', $authZegoId)
            ->where('receiver_id', $otherZegoId);
        })
        ->orWhere(function ($q) use ( $authZegoId, $otherZegoId) {
            $q->where('sender_id', $otherZegoId)
            ->where('receiver_id', $authZegoId);
        })
        ->count();
        return response()->json([
            'data' => $data,
            'next_cursor' => $messages->nextCursor()?->encode(),
            'prev_cursor' => $messages->previousCursor()?->encode(),
            'total_message' => $totalMessagesCount,
            'message' => 'Messages retrieved successfully'
        ]);
    }

    public function conversationList()
    {
        $currentUserId = auth('api')->user()->zego_user_id;

        $conversations = Conversation::with('lastMessage')
            ->where(function ($q) use ($currentUserId) {
                $q->where('user_one_id', $currentUserId)
                ->orWhere('user_two_id', $currentUserId);
            })
            ->latest('updated_at')
            ->get();

        $data = $conversations->map(function ($conversation) use ($currentUserId) {
            $chatUserId = $conversation->user_one_id == $currentUserId
                ? $conversation->user_two_id
                : $conversation->user_one_id;

            $chatUser = User::where('zego_user_id', $chatUserId)->first();

            if (!$chatUser) {
                return null;
            }

            $unreadCount = Message::where('sender_id', $chatUserId)
                ->where('receiver_id', (int)auth('api')->user()->zego_user_id)
                ->where('is_read', false)
                ->count();

            return [
                'user_id'         => (int) $chatUser->zego_user_id,
                'name'            => $chatUser->full_name ?? '',
                'profile_picture' => $chatUser->profile_picture ?? null,
                'last_message'    => $conversation->lastMessage->message ?? '',
                'message_type'    => $conversation->lastMessage->message_types ?? '',
                'timestamp'       => $conversation->lastMessage->created_at ?? $conversation->updated_at,
                'unread_count'    => $unreadCount,
                'is_online'       => $chatUser->is_online ?? false,
                'last_message_id' => $conversation->lastMessage->id ?? null,
            ];
        })->filter()->values();
        return $this->sendResponse($data, 'Conversation retrived successfully');
    }

    public function markAsRead($id)
    {
        $message = Message::where('id', $id)
                        ->where('receiver_id', auth('api')->user()->zego_user_id)
                        ->firstOrFail();

        $message->update(['is_read' => true]);

        return $this->sendResponse((object)[], 'Marked as read');
    }

    public function destroy($id)
    {
        $message = Message::where('id', $id)
                        ->where('sender_id', auth('api')->user()->zego_user_id)
                        ->firstOrFail();

        dispatch(new SendWebSocketUpdate(
            $message->receiver_id,
            'delete_message',
            ['message_id' => $message->id]
        ));
        
        $message->delete();
        return $this->sendResponse((object)[], 'Message deleted');
    }
}
