<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Album;
use GuzzleHttp\Client;
use App\Models\Artwork;
use App\Models\Activity;
use App\Models\Artboard;
use Illuminate\Http\Request;
use App\Jobs\RegistrationJob;
use App\Jobs\LoginActivityJob;
use App\Jobs\ChangePasswordJob;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client as PassportClient;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $userAgent = $request->header('User-Agent');

        if (!Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                // Dispatch the login failed activity to the queue
                LoginActivityJob::dispatch($user, false, 'Failed login attempt due to incorrect password.', 'Login Failed', $userAgent);
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Create a token for the user
        $token = $user->createToken('authToken');

        LoginActivityJob::dispatch($user, true, 'You successfully logged into your account', 'Login Successful', $userAgent);
        $profileUrl = $user->profile_compressed ? Storage::disk('s3')->url($user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';
        // Return the token and user details
        return response()->json([
            'username' => $user->username,
            'fullname' => $user->name,
            'token' => $token->accessToken,
            'profile' => $profileUrl,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $userAgent = $request->header('User-Agent');

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->full_name,
            'country' => $request->country,
            'password' => Hash::make($request->password),
        ]);

        RegistrationJob::dispatch($user, $userAgent);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful'
        ], 200);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],  // must pass 'new_password_confirmation' too
        ]);

        $user = Auth::user();
        $userAgent = $request->header('User-Agent');

        if (!Hash::check($request->current_password, $user->password)) {
            ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent);
            return response()->json([
                'message' => 'Current password is incorrect',
                'success' => false,
            ], 422);
        }
        ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent);
        return response()->json([
            'message' => 'Password updated successfully',
            'success' => true,
        ]);
    }

    public function fetchPasswordActivities()
    {
        // Get the current authenticated user
        $user = Auth::user();

        // Fetch password-related activities for the user (e.g., password change attempts)
        $activities = Activity::where('user_id', $user->id)
                            ->where('source', 'Authentication') // Filter activities related to authentication
                            ->orderBy('created_at', 'desc')   // Order by most recent first
                            ->get();

        // Check if activities exist
        if ($activities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No password-related activities found.',
            ], 404);
        }

        $formattedActivities = $activities->map(function ($activity) {
            return [
                'time' => $activity->created_at->format('M d, Y - h:m a'),
                'status' => $activity->status ? 'changed' : 'failed', // Assuming 'status' is boolean (true for successful, false for failed)
                'action' => $activity->title,  // Assuming the activity title is "Password Updated"
            ];
        });

        // Return activities
        return response()->json([
            'success' => true,
            'data' => $formattedActivities,
        ], 200);
    }
}
