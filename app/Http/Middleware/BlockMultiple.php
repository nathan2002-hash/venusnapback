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
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'blocked_404_' . $ip;

        if (cache()->has($key) && cache()->get($key) >= 5) {
            abort(403, 'Too many 404s from your IP');
        }

        $response = $next($request);

        // Count only if it's a 404
        if ($response->getStatusCode() === 404) {
            cache()->increment($key);
            cache()->put($key, cache()->get($key), now()->addMinutes(10));
        }

        return $response;
    }
}
