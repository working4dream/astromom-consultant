<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetUserDisplayTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $default = config('app.display_timezone', 'UTC');
        $tz = $default;

        $user = Auth::guard('api')->user() ?? Auth::guard('web')->user();
        if ($user && ! empty($user->timezone)) {
            $tz = $user->timezone;
        }

        if (! in_array($tz, timezone_identifiers_list(), true)) {
            $tz = $default;
        }

        config(['app.current_display_timezone' => $tz]);
        View::share('userDisplayTimezone', $tz);

        return $next($request);
    }
}
