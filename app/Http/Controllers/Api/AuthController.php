<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Album;
use App\Models\Artboard;
use App\Models\Artwork;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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

        // Attempt to authenticate
        if (!Auth::attempt($request->only('email', 'password'))) {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $activity = new Activity();
                $activity->title = 'Login Failed';
                $activity->description = 'Failed login attempt due to incorrect password.';
                $activity->source = 'Authentication';
                $activity->user_id = $user->id; // Log this against the existing user if email exists
                $activity->status = false;
                $activity->save();
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Create a token for the user
        $token = $user->createToken('authToken');

        $activity = new Activity();
        $activity->title = 'Login Successful';
        $activity->description = 'You successfully logged into your account';
        $activity->source = 'Authentication';
        $activity->user_id = Auth::id();
        $activity->status = true;
        $activity->save();

        // Return the token and user details
        return response()->json([
            'username' => $user->name,
            'token' => $token->accessToken,
            'profile' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&color=7F9CF5&background=EBF4FF",
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);


        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'username' => $request->full_name,
            'country' => $request->country,
            'status' => 'active',
            'password' => Hash::make($request->password),
        ]);

        $randomNumber = mt_rand(1000, 9999);

        $album = Album::create([
            'name' => $request->full_name . $randomNumber,
            'description' => "This is " . $request->full_name . "'s Album",
            'user_id' => $user->id,
            'type' => "General",
            'status' => "active",
            'slug' => "$user->full_name $randomNumber",
            'is_verified' => 0,
            'visibility' => "public",
            'logo' => "albums/rUSWa6xIDbTvpdf3sJcxCdWx0q02jyqyp8VAdXVj.jpg",
        ]);
        // Generate a token for the user
        $token = $user->createToken('authToken');



        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'album' => $album,
        ], 201);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],  // must pass 'new_password_confirmation' too
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            $activity = new Activity();
            $activity->title = 'Password Update Failed';
            $activity->description = 'Password change failed due to incorrect current password';
            $activity->source = 'Authentication';
            $activity->user_id = Auth::user()->id;
            $activity->status = false;
            $activity->save();
            return response()->json([
                'message' => 'Current password is incorrect',
                'success' => false,
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        $activity = new Activity();
        $activity->title = 'Password Updated';
        $activity->description = 'Your account password was changed';
        $activity->source = 'Authentication';
        $activity->user_id = Auth::user()->id;
        $activity->status = true;
        $activity->save();
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
                'time' => $activity->created_at->format('MMM dd, yyyy - hh:mm a'),
                'status' => $activity->status ? 'changed' : 'failed', // Assuming 'status' is boolean (true for successful, false for failed)
                'action' => 'Password Updated',  // Assuming the activity title is "Password Updated"
            ];
        });

        // Return activities
        return response()->json([
            'success' => true,
            'data' => $formattedActivities,
        ]);
    }
}
