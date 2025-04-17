<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        switch ($user->status) {
            case 'deletion':
                return response()->json(['error' => 'Your account is queued for deletion.'], 403);
            case 'locked':
                return response()->json(['error' => 'Your account is locked. Contact support.'], 403);
            case 'active':
                return $next($request); // Proceed
            default:
                return response()->json(['error' => 'Account status not recognized.'], 403);
        }
    }

}
