<?php

namespace App\Http\Controllers\API\v1;

use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;

class NotificationController extends BaseController
{
    public function getNotification(Request $request)
    {
        $notifications = Notification::where('user_id', auth('api')->user()->id)->orderBy('id', 'DESC')->paginate($request->per_page);
        if($notifications->isEmpty()){
            return $this->sendResponse([], 'Notification not found');
        }

        $data = $notifications->groupBy(function ($notification) {
            return group_day_label($notification->created_at);
        })->map(function ($group, $date) {
            return [
                'day' => $date,
                'data' => $group->map(function ($notification) {
                    $createdAt = Carbon::parse($notification->created_at);
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'subtitle' => $notification->subtitle,
                        'badge_title' => $notification->badge_title,
                        'image' => $notification->image,
                        'type' => $notification->type,
                        'link' => $notification->link,
                        'is_seen' => $notification->is_seen,
                        'time_ago' => $createdAt->diffInHours(now()) < 24 ? $createdAt->diffForHumans() : user_tz_format($notification->created_at, 'h:i A'),
                    ];
                }),
            ];
        })->values();
        return $this->sendResponse($data, 'Notification retrived successfully', $notifications);
    }

    public function markAsSeen(){
        $updated = Notification::where('user_id', auth('api')->user()->id)
                                ->where('is_seen', 0)
                                ->update(['is_seen' => 1]);

        return $this->sendResponse([], 'All notifications marked as seen');
    }

    public function delete($id){
        $notification = Notification::where('id',$id)->where('user_id', auth('api')->user()->id)->first();
        if($notification === NULL){
            return $this->sendResponse([], 'Notification not found');
        }
        $notification->delete();
        return $this->sendResponse([], 'Notification deleted successfully');
    }
}
