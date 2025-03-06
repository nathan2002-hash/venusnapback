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
        $album->type = 'personal'; // or some default if type is missing
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

    public function personalstore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'required|in:private,public',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        $album = new Album($validated);
        $album->user_id = Auth::user()->id;
        $album->type = 'personal';
        $album->name = $request->name;
        $album->description = $request->description;
        $album->visibility = $request->visibility;

        if ($request->hasFile('thumbnail')) {
            $album->thumbnail_original = $request->file('thumbnail')->store('thumbnails');
        }

        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json([
            'message' => 'Personal album created successfully!',
            'album' => $album
        ]);
    }

    public function creatorstore(Request $request)
    {
        // $validated = $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'visibility' => 'required|in:private,public,exclusive',
        //     'release_date' => 'nullable|date',
        //     'content_type' => 'nullable|string|max:50',
        //     'tags' => 'nullable|string',
        //     'allow_comments' => 'required|boolean',
        //     'enable_rating' => 'required|boolean',
        //     'thumbnail' => 'nullable|image|max:2048',
        // ]);

        $album = new Album();
        $album->user_id = Auth::user()->id;
        $album->type = 'creator';
        $album->name = $request->name;
        $album->description = $request->description;
        $album->visibility = $request->visibility;
        $album->release_date = $request->release_date;
        $album->content_type = $request->content_type;
        if ($album->allow_comments) {
            $album->allow_comments = 1;
        } else {
            $album->allow_comments = 0;
        }

       if ($album->enable_rating) {
        $album->enable_rating = 1;
       } else {
        $album->enable_rating = 0;
       }

        $album->tags = $request->tags;

        if ($request->hasFile('thumbnail')) {
            $album->thumbnail_original = $request->file('thumbnail')->store('thumbnails');
        }

        // Convert comma-separated tags into array if needed
        //$album->tags = explode(',', $request->input('tags'));

        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json([
            'message' => 'Creator album created successfully!',
            'album' => $album
        ]);
    }

    public function businessstore(Request $request)
    {
        // $validated = $request->validate([
        //     'name' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        //     'visibility' => 'required|in:private,public',
        //     'business_category' => 'required|string|max:100',
        //     'is_paid_access' => 'required',
        //     'price' => 'nullable|numeric|min:0',
        //     'phone' => 'nullable|string|max:20',
        //     'email' => 'nullable|email',
        //     'location' => 'nullable|string|max:255',
        //     'website' => 'nullable|url',
        //     'facebook' => 'nullable|url',
        //     'linkedin' => 'nullable|url',
        //     'business_logo' => 'nullable|image|max:2048',
        //     'cover_image' => 'nullable|image|max:4096',
        // ]);

        $album = new Album();
        $album->user_id = Auth::user()->id;
        $album->type = 'business';
        $album->name = $request->name;
        $album->description = $request->description;
        $album->business_category = $request->business_category;
        if ($album->is_paid_access) {
            $album->is_paid_access = 1;
        } else {
            $album->is_paid_access = 0;
        }

        $album->phone = $request->phone;
        $album->email = $request->email;
        $album->location = $request->location;
        $album->website = $request->website;
        $album->facebook = $request->facebook;
        $album->linkedin = $request->linkedin;

        if ($request->hasFile('business_logo')) {
            $album->business_logo_original = $request->file('business_logo')->store('logos');
        }

        if ($request->hasFile('cover_image')) {
            $album->cover_image_original = $request->file('cover_image')->store('covers');
        }

        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json([
            'message' => 'Business album created successfully!',
            'album' => $album
        ]);
    }
}
