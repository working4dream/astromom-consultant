<?php

namespace App\Http\Controllers\API\v2;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;

class ChatController extends BaseController
{
    public function conversationList()
    {
        $currentUserId = auth('api')->user()->zego_user_id;

        $conversations = Conversation::with('lastMessage')
            ->where(function ($q) use ($currentUserId) {
                $q->where('user_one_id', $currentUserId)
                ->orWhere('user_two_id', $currentUserId);
            })
            ->latest('updated_at')
            ->paginate(10);

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
        return $this->sendResponse($data, 'Conversation retrived successfully', $conversations);
    }
}
