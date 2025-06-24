<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlockMultiple
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $user = Auth::user();

        // Use IP or user ID for throttling key
        $key = $user ? '404:user:' . $user->id : '404:ip:' . $ip;

        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_NOT_FOUND) {
            $limit = $user ? 20 : 5;
            $attempts = Cache::increment($key);
            Cache::put($key, $attempts, now()->addMinutes(10));

            if ($attempts >= $limit) {
                return response()->json([
                    'message' => 'Too many invalid requests. Try again later.'
                ], 429); // Too Many Requests
            }
        }

        return $response;
    }
}
