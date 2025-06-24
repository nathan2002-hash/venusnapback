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

        // ✅ Skip /blocked route to avoid redirect loop
        if ($path === 'blocked') {
            return $next($request);
        }

        $response = $next($request);
        $status = $response->getStatusCode();

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

        $minutes = $userId ? 5 : 5;
        $limit = $userId ? 20 : 5;

        $recentAttempts = DB::table('blocked_requests')
            ->where('ip', $ip)
            ->when($userId, fn($q) => $q->orWhere('user_id', $userId))
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();

        if ($recentAttempts >= $limit) {
            return response()->view('auth.blocked', [], 429); // 👈 Show view instead of redirect
        }

        return $response;
    }
}
