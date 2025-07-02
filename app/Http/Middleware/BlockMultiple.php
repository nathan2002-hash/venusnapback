<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class BlockMultiple
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     protected array $blockedPatterns = [
        'wp-includes', 'wp-content', 'wp-admin',
        '.well-known', 'templates', 'wp-signup.php',
        'xmlrpc.php', 'wlwmanifest.xml', 'block-patterns',
        'index.php', 'users.php', 'file.php',
        'radio.php', '1.php', 'wso.php', 'include.php',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = strtolower($request->path());

        // 1. Block by known attack patterns
        foreach ($this->blockedPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return response('Blocked – suspicious path', 403);
            }
        }

        // 2. Block if path doesn't exist in route list
        if (!$this->isValidRoute($request)) {
            return response()->view('errors.404', [], 404);
        }

        return $next($request);
    }

    protected function isValidRoute(Request $request): bool
    {
        // Match only GET routes (skip POST, etc. to avoid false negatives)
        $method = $request->method();
        $path = '/' . ltrim($request->path(), '/');

        foreach (Route::getRoutes() as $route) {
            if (in_array($method, $route->methods()) && $route->matches($request)) {
                return true;
            }
        }

        return false;
    }
}
