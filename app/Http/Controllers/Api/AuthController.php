<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (Auth::attempt($credentials)) {
    //         $user = Auth::user();
    //         $token = $user->createToken('authToken')->plainTextToken;

    //         return response()->json([
    //             'status' => 'success',
    //             'user' => $user,
    //             'token' => $token,
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 'error',
    //         'message' => 'Invalid credentials',
    //     ], 401);
    // }

    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve the authenticated user
        $user = Auth::user();

        // Create a token for the user
        $token = $user->createToken('authToken');

        // Return the token and user details
        return response()->json([
            'username' => $user->name,
            'email' => $user->email,
            'artboard' => $user->artboard->name,
            'artboard_description' => $user->artboard->description,
            'token' => $token->accessToken,
            'userid' => (string) $user->id,
            'profile' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&color=7F9CF5&background=EBF4FF",
            'artboard_profile' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&color=7F9CF5&background=EBF4FF",
        ]);
    }

    public function googleLogin(Request $request)
    {
        $idToken = $request->input('idToken');

    // Verify ID token with Google
    $response = Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");
    $googleUser = $response->json();

    if (!isset($googleUser['email'])) {
        return response()->json(['error' => 'Invalid Google token'], 401);
    }

    // Check if user exists, otherwise create a new one
    $user = User::firstOrCreate(
        ['email' => $googleUser['email']],
        [
            'name' => $googleUser['name'],
            'password' => bcrypt(uniqid()), // Dummy password, not needed
            'profile_photo_path' => $googleUser['picture'],
        ]
    );

    // Generate token
    $token = $user->createToken('authToken');
    //$token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'username' => $user->name,
        'profile' => $user->profile_photo_path,
        'email' => $user->email,
        'userid' => $user->id,
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
            'password' => Hash::make($request->password),
        ]);

        $artboard = Artboard::create([
            'name' => $request->full_name,
            'description' => "This is " . $request->full_name . "'s Artboard",
            'user_id' => $user->id,
            'type' => "General",
            'slug' => "General$user->name",
            'is_verified' => 0,
            'visibility' => "public",
            'logo' => "artboards/rUSWa6xIDbTvpdf3sJcxCdWx0q02jyqyp8VAdXVj.jpg",
        ]);
        // Generate a token for the user
        $token = $user->createToken('authToken');



        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'artboard' => $artboard,
        ], 201);
    }

}
