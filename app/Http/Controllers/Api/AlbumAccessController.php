<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AlbumAccessController extends Controller
{
    public function accesslist($id)
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


    public function al($id)
    {
        $album = Auth::user()->albums()->findOrFail($id);
        return response()->json(['album' => $album]);
    }

    public function albums($id)
{
    $album = Album::with(['posts.postmedias'])
        ->find($albumId);

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
            'posts' => $posts,
            'email' => $album->email,
            'phone' => $album->phone,
            'facebook' => $album->facebook,
            'linkedin' => $album->linkedin,
            'website' => $album->website,
            'business_category' => $album->category_id,
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

}
