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
        $album = Auth::user()->albums()->with('posts')->findOrFail($id);
        return response()->json(['album' => $album]);
    }

    public function albumupdate(Request $request, $id)
    {
        $album = Auth::user()->albums()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'shared_with' => 'array',
            'shared_with.*' => 'email|exists:users,email'
        ]);

        $album->update($validated);

        // Sync shared users
        $userIds = User::whereIn('email', $request->shared_with)->pluck('id');
        $album->sharedWith()->sync($userIds);

        return response()->json(['message' => 'Album updated']);
    }
}
