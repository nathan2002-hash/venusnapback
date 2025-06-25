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
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ip = $realIp;
        $user = Auth::user();
        $userId = $user?->id;
        $path = $request->path();

        // ✅ Block missing User-Agent only for guests
        if (!$userId && !$request->header('User-Agent')) {
            return response('Missing User-Agent header.', 403);
        }

        // ✅ Skip /blocked route to avoid loop
        if ($path === 'blocked') {
            return $next($request);
        }

        $response = $next($request);
        $status = $response->getStatusCode();

        // ✅ Log only non-successful responses
        if (!($status >= 200 && $status <= 299)) {
            DB::table('blocked_requests')->insert([
                'ip' => $ip,
                'user_id' => $userId,
                'url' => $request->fullUrl(),
                'status_code' => $status,
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$userId) {
            $minutes = 5;
            $limit = 5;

            $recentAttempts = DB::table('blocked_requests')
                ->where('ip', $ip)
                ->where('created_at', '>=', now()->subMinutes($minutes))
                ->count();

            if ($recentAttempts >= $limit) {
                return response()->view('auth.blocked', [], 429);
            }
        }

        return $response;
    }

}
