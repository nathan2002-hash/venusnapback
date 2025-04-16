<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Album;
use App\Models\AlbumAccess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    $user = Auth::user();

    // Check if the user is the owner or has editor/owner access to the album
    $hasAccess = Album::where('id', $id)
        ->where(function ($query) use ($user) {
            $query->where('user_id', $user->id) // Album owner
                  ->orWhereIn('id', function ($subQuery) use ($user) {
                      $subQuery->select('album_id')
                          ->from('album_accesses')
                          ->where('user_id', $user->id)
                          ->where('status', 'approved')
                          ->whereIn('role', ['editor', 'owner']); // Only allow editors or owners
                  });
        })
        ->exists();

    if (! $hasAccess) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Fetch the access list (including email)
    $accessList = DB::table('album_accesses')
        ->join('users', 'album_accesses.user_id', '=', 'users.id')
        ->where('album_accesses.album_id', $id)
        ->select('users.email', 'album_accesses.role', 'album_accesses.status', 'album_accesses.created_at')
        ->get();

    return response()->json([
        'access_list' => $accessList
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
    $user = $request->user();
    
    $requests = AlbumAccess::with(['album', 'requester'])
        ->where(function($query) use ($user) {
            $query->where('granted_by', $user->id) // Requests you need to approve
                ->orWhere('user_id', $user->id); // Requests you've made
        })
        ->where('status', 'pending')
        ->get()
        ->map(function ($access) {
            return [
                'id' => $access->id,
                'album' => [
                    'id' => $access->album->id,
                    'name' => $access->album->name,
                ],
                'requester' => [
                    'id' => $access->requester->id,
                    'name' => $access->requester->name,
                    'avatar' => $access->requester->avatar_url,
                ],
                'role' => $access->role,
                'status' => $access->status,
                'created_at' => $access->created_at->diffForHumans(),
            ];
        });

    return response()->json(['requests' => $requests]);
}

}
