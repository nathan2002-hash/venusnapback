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
        // Find the album and ensure the logged-in user owns it
        $album = Auth::user()->albums()->findOrFail($id);

        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_category' => 'required|string',
            'shared_with' => 'array',
            'shared_with.*' => 'email|exists:users,email',
        ]);

        // Update the album details (name, description, business_category)
        $album->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'business_category' => $validated['business_category'],
        ]);

        // Process the shared_with emails
        foreach ($request->shared_with as $email) {
            // Find the user by email
            $user = User::where('email', $email)->first();

            if ($user) {
                // Check if the user is already in the album_access table
                $albumAccess = $album->sharedWith()->where('user_id', $user->id)->first();

                if ($albumAccess) {
                    // If the access record exists, update it
                    // $albumAccess->update([
                    //     'status' => 'pending' // You can set the status to "updated" or any other value
                    // ]);
                } else {
                    // If no access record exists, create a new one
                    $album->sharedWith()->create([
                        'user_id' => $user->id,
                        'album_id' => $album->id,
                        'granted_by' => Auth::user()->id,
                        'status' => 'pending', // Set a default status for new access
                        'role' => 'editor', // Set a default status for new access
                    ]);
                }
            }
        }

        // Return success response
        return response()->json(['message' => 'Album updated successfully']);
    }

}
