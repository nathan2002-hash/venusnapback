<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artboard;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    public function index(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated user'
            ], 401);
        }
        $artboard = $user->artboard;
        // Return the user profile data
        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile' => Storage::disk('s3')->url($user->profile_photo_path),
            //     'profile' => $user->profile_photo_path
            // ? asset('storage/' . $user->profile_photo_path)
            // : asset('default/profile.png'),
                'date_joined' => $user->created_at->format('j F, Y'),
                'total_posts' => (string) $user->posts->count(), // Count user's posts
                'total_admires' => (string) $this->formatNumber(
                        $user->posts->sum(fn($post) =>
                            $post->postmedias->sum(fn($media) => $media->admires->count())
                        )
                    ),
                'artboard' => $artboard ? [
                    'name' => $artboard->name,
                    'slug' => $artboard->slug,
                    'description' => $artboard->description,
                    'type' => $artboard->type,
                    'supporters' => $artboard->supporters->count(),
                    'is_verified' => (bool) $artboard->is_verified,
                    'visibility' => $artboard->visibility,
                    'logo' => Storage::disk('s3')->url($artboard->logo),
                //     'logo' => $artboard->logo
                // ? asset('storage/' . $artboard->logo)
                // : asset('default/artboard_logo.png'),
                ] : null
            ]
        ]);
    }

    public function update(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'error' => 'Unauthenticated user'
        ], 401);
    }

    // Validate the request
    $request->validate([
        'username' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'artboard' => 'required|string|max:255',
        'artboarddescription' => 'required|string|max:500',
        'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'artboard_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Update user data
    $user->name = $request->username;
    $user->email = $request->email;

    // Save profile image
    if ($request->hasFile('profile')) {
        $profilePath = $request->file('profile')->store('profiles', 's3');
        $user->profile_photo_path = $profilePath;
    }

    $user->save();

    // Update artboard data
    $artboard = Artboard::find($user->artboard->id);
    $artboard->name = $request->artboard;
    $artboard->description = $request->artboarddescription;

    // Save artboard logo
    if ($request->hasFile('artboard_profile')) {
        $artboardLogoPath = $request->file('artboard_profile')->store('artboards/logos/', 's3');
        $artboard->logo = $artboardLogoPath;
    }

    $artboard->save();

    return response()->json([
        'message' => 'Updated Successfully'
    ], 200);
}

    private function formatNumber($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return $number; // If less than 1000, return as is
    }


}
