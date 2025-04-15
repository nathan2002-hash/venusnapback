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


    public function albums($id)
    {
        $album = Auth::user()->albums()->findOrFail($id);
        return response()->json(['album' => $album]);
    }

   public function albumupdate(Request $request, $id)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'message' => 'Authentication required',
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Find the album
            $album = Auth::user()->albums()->find($id);

            // Check if album exists
            if (!$album) {
                return response()->json([
                    'message' => 'Album not found or you don\'t have permission to edit it',
                    'error' => 'Album not found'
                ], 404);
            }

            // Validate the request data
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'business_category' => 'required|string',
                'shared_with' => 'array',
                'shared_with.*' => 'email|exists:users,email',
            ]);

            // Update the album details
            $album->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'business_category' => $validated['business_category'],
            ]);

            // Process the shared_with emails
            $sharedUsers = [];
            $notFoundUsers = [];
            foreach ($request->shared_with as $email) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    $notFoundUsers[] = $email;
                    continue;
                }

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
                'message' => 'Album updated successfully',
                'data' => [
                    'album' => $album->fresh(),
                    'shared_with' => $sharedUsers
                ]
            ];

            if (!empty($notFoundUsers)) {
                $response['warnings'] = [
                    'message' => 'Some users were not found',
                    'not_found_users' => $notFoundUsers
                ];
            }
            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update album',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
