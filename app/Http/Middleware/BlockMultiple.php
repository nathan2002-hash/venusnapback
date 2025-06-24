<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class BlockMultiple
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $statusCode = $response->getStatusCode();

        // Ignore successful responses
        if ($statusCode >= 200 && $statusCode < 300) {
            return $response;
        }

        $ip = $request->ip();
        $user = $request->user();
        $userId = optional($user)->id;
        $url = $request->fullUrl();
        $userAgent = $request->userAgent();

        // Log every failed request
        DB::table('blocked_requests')->insert([
            'ip' => $ip,
            'user_id' => $userId,
            'url' => $url,
            'user_agent' => $userAgent,
            'status_code' => $statusCode,
            'attempts' => 1,
            'last_attempt_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Count attempts in the last 10 minutes
        $timeLimit = now()->subMinutes(10);
        $query = DB::table('blocked_requests')
            ->where('ip', $ip)
            ->where('last_attempt_at', '>=', $timeLimit);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attemptCount = $query->count();

        // Block if threshold exceeded
        $maxAttempts = $userId ? 20 : 5;

        if ($attemptCount >= $maxAttempts) {
            abort(403, 'Too many failed requests. Please wait 10 minutes.');
        }

        return $response;
    }

}
