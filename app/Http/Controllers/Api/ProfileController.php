<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Artboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\ProfileUpdate;
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

        $coverUrl = $user->cover_compressed
        ? Storage::disk('s3')->url($user->cover_compressed)
        : config('app.default_cover_url');
        $profileUrl = $user->profile_compressed ? Storage::disk('s3')->url($user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';
        // Return the user profile data

        $totalSupporters = $user->albums->reduce(function ($carry, $album) {
            return $carry + $album->supporters()->count(); // assuming 'supporters' is a relationship on the Album model
        }, 0);

        return response()->json([
            'user' => [
                'fullname' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'profile' => $profileUrl,
                'cover' => $coverUrl,
                'date_joined' => $user->created_at->format('j F, Y'),
                'total_posts' => (string) $user->posts->count(),
                'total_albums' => (string) $user->albums->count(),
                'supporters' => (string) $totalSupporters,
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
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'full_name' => 'required|string|max:255',
            'country' => 'required|string|max:500',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20000',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20000',
        ]);

        $user = User::find($user->id);
        // Update user data
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone_number;
        $user->name = $request->full_name;
        $user->country = $request->country;
        $user->dob = $request->dob;
        $user->gender = $request->gender;

        // Save profile image
        if ($request->hasFile('profile')) {
            $profilePath = $request->file('profile')->store('uploads/profiles/originals/profile', 's3');
            $user->profile_original = $profilePath;
        }

        if ($request->hasFile('cover_photo')) {
            $coverPath = $request->file('cover_photo')->store('uploads/profiles/originals/cover', 's3');
            $user->cover_original = $coverPath;
        }

        $user->save();

        ProfileUpdate::dispatch($user);

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

    public function changeprofile(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated user'
            ], 401);
        }

        $profileUrl = $user->profile_compressed ? Storage::disk('s3')->url($user->profile_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';
        $coverUrl = $user->cover_compressed ? Storage::disk('s3')->url($user->cover_compressed) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';
        // Return the user profile data
        return response()->json([
            'user' => [
                'fullname' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'country' => $user->country,
                'profile' => $profileUrl,
                'cover_photo' => $coverUrl,
                'gender' => $user->gender,
                'dob' => $user->dob,
            ]
        ]);
    }

    public function changeprofsile(Request $request)
    {
        // Get the currently authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated user'
            ], 401);
        }
        $artboard = $user->artboard;

        $profileUrl = $user->profile_photo_path ? Storage::disk('s3')->url($user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';

    // Check if artboard logo exists, otherwise use default 100x100 avatar
    $logoUrl = $artboard && $artboard->logo ? Storage::disk('s3')->url($artboard->logo) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=100&d=mp';
        // Return the user profile data
        return response()->json([
            'user' => [
                'fullname' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'profile' => $profileUrl,
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
                    'logo' => $logoUrl,
                ] : null
            ]
        ]);
    }


}
