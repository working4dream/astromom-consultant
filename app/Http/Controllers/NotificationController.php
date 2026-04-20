<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Jobs\SendUserNotification;

class NotificationController extends Controller
{
    public function index(Request $request) 
    {
        $query = User::query()->select(['id', 'first_name', 'last_name', 'email', 'mobile_number', 'profile_picture'])
                    ->whereNotNull('device_token')
                    ->whereHas('roles', function ($q) {
                        $q->where('name', 'customer');
                    });

        if ($request->filled('full_name')) {
            $fullName = trim($request->full_name);
        
            if (str_contains($fullName, ' ')) {
                $query->whereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$fullName}%"])
                        ->orWhere('users.first_name', 'LIKE', "%{$fullName}%");
            } else {
                $query->where(function ($user) use ($fullName) {
                    $user->where('users.first_name', 'LIKE', "%{$fullName}%")
                          ->orWhere('users.last_name', 'LIKE', "%{$fullName}%");
                });
            }
        }

        if ($request->filled('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }

        $users = $query->orderByDesc('id')->paginate(20);
        return view('notification.index',compact('users'));
    }

    public function sendNotifications(Request $request)
    {
        if ($request->select_all_users == "1") {
            if ($request->user_type === 'customer') {
                $users = User::role('customer')->get();
            } elseif ($request->user_type === 'expert') {
                $users = User::role('astrologer')->get();
            } else {
                $users = User::all();
            }
        } else {
            $selectedUsers = json_decode($request->selected_users, true);
            $selectedUserIds = array_map('intval', $selectedUsers);
            $users = User::whereIn('id', $selectedUserIds)->get();
        }
        foreach ($users as $user) {
            SendUserNotification::dispatch($user, $request->title, $request->body,$request->button_text,$request->link);
        }
        return redirect()->back()->with('success', 'Notification sent successfully');
    }
}
