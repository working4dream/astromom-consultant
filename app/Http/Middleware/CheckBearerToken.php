<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a bearer token is provided
        if (!$token = $request->bearerToken()) {
            return response()->json([
                'message' => 'Token not provided',
                'status' => false
            ], 401);
        }

        // Attempt to authenticate customer using the 'customers' guard
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'message' => 'Unauthorized',
                'status' => false
            ], 401);
        }
        return $next($request);
    }
}
