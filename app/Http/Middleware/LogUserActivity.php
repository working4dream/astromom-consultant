<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\UserActivity;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('api')->check()) {
            $userId = Auth::guard('api')->id();
            UserActivity::where('user_id', $userId)->delete();
            UserActivity::create([
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'device_name' => Auth::guard('api')->user()->device_name,
                'last_activity' => Carbon::now(),
            ]);
        }
        
        return $next($request);
    }
}
