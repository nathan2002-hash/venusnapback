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
use Illuminate\Support\Facades\Log;
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
        $deviceinfo = $request->header('Device-Info');
        $ipaddress = $request->ip();

        if (!Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                // Dispatch the login failed activity to the queue
                LoginActivityJob::dispatch($user, false, 'Failed login attempt due to incorrect password.', 'Login Failed', $userAgent, $ipaddress, $deviceinfo);
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Create a token for the user
        $token = $user->createToken('authToken');

        LoginActivityJob::dispatch($user, true, 'You successfully logged into your account', 'Login Successful', $userAgent, $ipaddress, $deviceinfo);
        $profileUrl = $user->profile_compressed ? Storage::disk('s3')->url($user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';

        $preference = ($user->preference === null || $user->preference == 1) ? 1 : 0;
        // Return the token and user details
        return response()->json([
            'username' => (string) $user->username,
            'fullname' => $user->name,
            'token' => $token->accessToken,
            'preference' => (string) $preference,
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
        $deviceinfo = $request->header('Device-Info');
        $ipaddress = $request->ip();

        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->full_name,
            'country' => $request->country,
            'preference' => '1',
            'password' => Hash::make($request->password),
        ]);

        RegistrationJob::dispatch($user, $userAgent, $deviceinfo, $ipaddress);

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
        $deviceinfo = $request->header('Device-Info');
        $ipaddress = $request->ip();

        if (!Hash::check($request->current_password, $user->password)) {
            ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent, $ipaddress, $deviceinfo);
            return response()->json([
                'message' => 'Current password is incorrect',
                'success' => false,
            ], 422);
        }
        ChangePasswordJob::dispatch($user, $request->current_password, $request->new_password, $userAgent, $deviceinfo, $ipaddress);
        return response()->json([
            'message' => 'Password updated successfully',
            'success' => true,
        ]);
    }

    public function getLoginActivities(Request $request)
{
    // Fetch activities where type is 'authentication'
    $activities = Activity::where('type', 'authentication')
        ->where('user_id', $request->user()->id) // Fetch activities for the logged-in user
        ->select('device_info', 'ipaddress', 'created_at')
        ->get();

    // Add location to each activity
    $activities->transform(function ($activity) {
        $ip = $activity->ipaddress;
        if ($ip) {
            $location = $this->getLocationFromIP($ip); // Get location from IP
            $activity->location = $location;
        } else {
            $activity->location = 'Unknown Location'; // Handle missing IP address
        }
        return $activity;
    });

    return response()->json(['activities' => $activities]);
}

// Helper function to get location from IP
private function getLocationFromIP($ip)
{
    try {
        $response = Http::get("http://ipinfo.io/{$ip}/json");
        if ($response->successful()) {
            $data = $response->json();
            return $data['city'] . ', ' . $data['country'];
        }
    } catch (\Exception $e) {
        // Log the error (optional)
        Log::error("Failed to resolve location for IP {$ip}: " . $e->getMessage());
    }
    return 'Unknown Location';
}
}
