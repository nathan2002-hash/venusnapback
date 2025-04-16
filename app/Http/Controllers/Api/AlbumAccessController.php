<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Album;
use App\Models\AlbumAccess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AlbumAccessController extends Controller
{
    public function accesdslist($id)
    {
        // Ensure the logged-in user owns this album
        $album = Auth::user()->albums()->findOrFail($id);

        // Get the list of users who have access to the album
        $accessList = $album->sharedWith()
                            ->join('users', 'album_accesses.user_id', '=', 'users.id') // Join users table
                            ->pluck('users.email'); // Pluck email from the users table

        return response()->json([
            'access_list' => $accessList
        ]);
    }

    public function accesslist($id)
{
    $userId = Auth::id();

    // Check if the user is either the owner or has approved access to this album
    $hasAccess = DB::table('albums')
        ->where('id', $id)
        ->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhereExists(function ($sub) use ($userId) {
                      $sub->select(DB::raw(1))
                          ->from('album_accesses')
                          ->whereColumn('album_accesses.album_id', 'albums.id')
                          ->where('album_accesses.user_id', $userId)
                          ->where('album_accesses.status', 'approved');
                  });
        })
        ->exists();

    if (!$hasAccess) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Return all approved emails for the album
    $emails = DB::table('album_accesses')
        ->join('users', 'album_accesses.user_id', '=', 'users.id')
        ->where('album_accesses.album_id', $id)
        ->where('album_accesses.status', 'approved')
        ->pluck('users.email');

    return response()->json([
        'access_list' => $emails
    ]);
}

    public function al($id)
    {
        $album = Auth::user()->albums()->findOrFail($id);
        return response()->json(['album' => $album]);
    }

    public function albums($id)
{
    $album = Album::findOrFail($id);
    if (!$album) {
        return response()->json([
            'message' => 'Album not found'
        ], 404);
    }

    // ... (existing code for thumbnails, etc.)

    return response()->json([
        'album' => [
            'id' => $album->id,
            'name' => $album->name,
            'description' => $album->description,
            'type' => $album->type,
            'is_verified' => (bool)$album->is_verified,
            'is_owner' => auth()->id() === $album->user_id, // Add this line
            'supporters' => $album->supporters->count(),
            'email' => $album->email,
            'phone' => $album->phone,
            'facebook' => $album->facebook,
            'linkedin' => $album->linkedin,
            'website' => $album->website,
            'business_category' => $album->type == 'business'
            ? $album->category_id
            : ($album->type == 'creator' ? $album->content_type : null),
        // Include category name for display
        'category_name' => $album->type == 'business'
            ? ($album->category->name ?? null)
            : ($album->type == 'creator' ? $album->contentType->name ?? null : null),
        ]
    ], 200);
}

   public function albumupdate(Request $request, $id)
{
    try {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error' => 'User not authenticated'
            ], 401);
        }

        $album = Auth::user()->albums()->find($id);
        if (!$album) {
            return response()->json([
                'success' => false,
                'message' => 'Album not found or you don\'t have permission to edit it',
                'error' => 'Album not found'
            ], 404);
        }

        // First validate email existence
        $invalidEmails = [];
        foreach ($request->shared_with ?? [] as $email) {
            if (!User::where('email', $email)->exists()) {
                $invalidEmails[] = $email;
            }
        }

        // Validate other fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_category' => 'required|string',
            'shared_with' => 'array',
            'shared_with.*' => 'email', // Removed exists check since we handle it manually
        ]);

        // Update album
        $album->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'business_category' => $validated['business_category'],
           'phone' => $request->phone,
           'email' => $request->email,
           'website' => $request->website,
           'facebook' => $request->facebook,
           'linkedin' => $request->linkedin,
        ]);

        // Process sharing only with valid emails
        $sharedUsers = [];
        foreach ($request->shared_with as $email) {
            $user = User::where('email', $email)->first();
            if (!$user) continue;

            $existingAccess = $album->sharedWith()
                ->where('user_id', $user->id)
                ->first();

            if (!$existingAccess) {
                $album->sharedWith()->create([
                    'user_id' => $user->id,
                    'album_id' => $album->id,
                    'granted_by' => Auth::id(),
                    'status' => 'pending',
                    'role' => 'editor',
                ]);
            }
            $sharedUsers[] = $user->email;
        }

        $response = [
            'success' => true,
            'message' => 'Album updated successfully',
            'data' => [
                'album' => $album->fresh(),
                'shared_with' => $sharedUsers
            ]
        ];

        if (!empty($invalidEmails)) {
            $response['warnings'] = [
                'message' => 'Some users were not found',
                'invalid_emails' => $invalidEmails
            ];
        }

        return response()->json($response);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update album',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getRequests(Request $request)
{
    $userId = $request->user()->id;

    $requests = DB::table('album_accesses')
        ->join('albums', 'album_accesses.album_id', '=', 'albums.id')
        ->leftJoin('users as requesters', 'album_accesses.user_id', '=', 'requesters.id')
        ->leftJoin('users as granters', 'album_accesses.granted_by', '=', 'granters.id')
        ->where(function($query) use ($userId) {
            $query->where('album_accesses.granted_by', $userId)
                  ->orWhere('album_accesses.user_id', $userId);
        })
        ->where('album_accesses.status', 'pending')
        ->select(
            'album_accesses.id',
            'album_accesses.role',
            'album_accesses.status',
            'album_accesses.created_at',
            'albums.id as album_id',
            'albums.name as album_name',
            'requesters.id as requester_id',
            'requesters.name as requester_name',
            'requesters.email as requester_email',
            'requesters.profile_compressed as requester_profile',
            'granters.id as granter_id',
            'granters.name as granter_name',
            'granters.email as granter_email',
            'granters.profile_compressed as granter_profile'
        )
        ->orderByDesc('album_accesses.created_at')
        ->get()
        ->map(function ($access) use ($userId) {
            // If I'm the requester
            $isRequester = $access->requester_id == $userId;

            $avatarEmail = $isRequester ? $access->granter_email : $access->requester_email;
            $avatarProfile = $isRequester ? $access->granter_profile : $access->requester_profile;
            $avatarUrl = $avatarProfile
                ? Storage::disk('s3')->url($avatarProfile)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($avatarEmail))) . '?s=100&d=mp';

            return [
                'id' => $access->id,
                'album' => [
                    'id' => $access->album_id,
                    'name' => $access->album_name,
                ],
                'requester' => [
                    'id' => $isRequester ? $access->granter_id : $access->requester_id,
                    'name' => $isRequester ? $access->granter_name : $access->requester_name,
                    'avatar' => $avatarUrl,
                ],
                'role' => $access->role,
                'status' => $access->status,
                'created_at' => Carbon::parse($access->created_at)->diffForHumans(),
            ];
        });

    return response()->json(['requests' => $requests]);
}

public function respondToRequest(Request $request, $id)
{
    $validated = $request->validate([
        'action' => 'required|in:approve,reject'
    ]);

    $albumAccess = AlbumAccess::findOrFail($id);

    // Verify user has permission to respond
    if ($albumAccess->granted_by != $request->user()->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $albumAccess->status = $validated['action'] == 'approve' ? 'approved' : 'rejected';
    $albumAccess->save();

    return response()->json(['message' => 'Request updated']);
}


}
