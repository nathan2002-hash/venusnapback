<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\AlbumCreate;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'required|in:public,private',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $user = Auth::user();

        // Check if album name already exists
        if (Album::where('user_id', $user->id)->where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Album name already exists'], 409);
        }

        // Save Album (without thumbnail path for now)
        $album = new Album();
        $album->user_id = $user->id;
        $album->name = $request->name;
        $album->description = $request->description;
        $album->type = 'album'; // or some default if type is missing
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('uploads/albums/originals', 's3');
        }
        $album->original_cover = $path;
        $album->visibility = $request->visibility;
        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 200);
    }


    public function getUserAlbums()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Fetch albums associated with the user
        $albums = Album::where('user_id', $user->id)
            ->select('id', 'name') // Only fetch necessary fields
            ->get();

        return response()->json([
            'success' => true,
            'data' => $albums,
        ]);
    }
}
