<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Jobs\AlbumCreate;
use Illuminate\Http\Request;
use App\Models\AlbumCategory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            ->select('id', 'name', 'type') // Only fetch necessary fields
            ->get();

        return response()->json([
            'success' => true,
            'data' => $albums->map(function ($album) {
                if ($album->type == 'personal') {
                    $albumName = "{$album->name} (Personal Album)";
                } elseif ($album->type == 'creator') {
                    $albumName = "{$album->name} (Creator Album)";
                } else {
                    $albumName = "{$album->name} (Business Album)";
                }

                return [
                    'id' => $album->id,
                    'album_name' => $albumName,
                ];
            }),
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
            $path = $request->file('thumbnail')->store('uploads/albums/originals', 's3');
        }
        $album->thumbnail_original = $path;

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

       $album->tags = json_decode($request->tags, true);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('uploads/albums/originals', 's3');
        }
        $album->thumbnail_original = $path;

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
            $bpath = $request->file('business_logo')->store('uploads/albums/originals', 's3');
        }
        if ($request->hasFile('cover_image')) {
            $cpath = $request->file('cover_image')->store('uploads/albums/originals', 's3');
        }
        $album->business_logo_original = $bpath;
        $album->cover_image_original = $cpath;

        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json([
            'message' => 'Business album created successfully!',
            'album' => $album
        ]);
    }

    public function albumcategorycreator()
    {
        $categories = AlbumCategory::select('id', 'name')
                        ->where('type', 'creator') // Filter only "creator" categories
                        ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function albumcategorybusiness()
    {
        $categories = AlbumCategory::select('id', 'name')
                        ->where('type', 'business') // Filter only "creator" categories
                        ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    public function getAlbums(Request $request)
    {
        // Fetch albums for the logged-in user
        $albums = Album::where('user_id', $request->user()->id)
            ->select('id', 'name', 'description', 'thumbnail_original', 'business_logo_original', 'business_logo_compressed', 'thumbnail_compressed', 'type', 'created_at')
            ->get();

        // Modify each album to include the proper S3 URL for thumbnails
        $albums = $albums->map(function ($album) {
            // Get the appropriate thumbnail based on the album type
            $thumbnailUrl = null;

            if ($album->type == 'personal' || $album->type == 'creator') {
                // For personal and creator, use the compressed thumbnail if it exists, otherwise use the original
                $thumbnailUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : null);
            } elseif ($album->type == 'business') {
                // For business albums, you may want to use the business logo thumbnail if available
                $thumbnailUrl = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : null);
            }

            return [
                'id' => $album->id,
                'name' => $album->name,
                'description' => $album->description,
                'type' => $album->type,
                'supporters' => $album->supporters->count(),
                'posts' => $album->posts->count(),
                'thumbnail_url' => $thumbnailUrl, // Include the S3 URL for the thumbnail
                'created_at' => $album->created_at->format('l, d F Y at h:i A'), // Format the date nicely
            ];
        });

        return response()->json(['albums' => $albums]);
    }

    public function show($albumId)
    {
        $album = Album::with(['posts.postmedias'])->find($albumId);

        if (!$album) {
            return response()->json([
                'message' => 'Album not found'
            ], 404);
        }

        // Determine the album's thumbnail
        if ($album->type == 'personal' || $album->type == 'creator') {
            $thumbnailUrl = $album->thumbnail_compressed
                ? Storage::disk('s3')->url($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? Storage::disk('s3')->url($album->thumbnail_original)
                    : null);
        } elseif ($album->type == 'business') {
            $thumbnailUrl = $album->business_logo_compressed
                ? Storage::disk('s3')->url($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? Storage::disk('s3')->url($album->business_logo_original)
                    : null);
        } else {
            $thumbnailUrl = 'https://example.com/default-thumbnail.jpg';
        }

        $bgthumbnailUrl = $album->cover_image_compressed
                ? Storage::disk('s3')->url($album->cover_image_compressed)
                : ($album->cover_image_original
                    ? Storage::disk('s3')->url($album->cover_image_original)
                    : null);

        // Attach post thumbnail from postmedias
        $posts = $album->posts->map(function ($post) {
            $postThumbnail = $post->postmedias->first() ? Storage::disk('s3')->url($post->postmedias->first()->file_path_compress) : null;
            return [
                'id' => $post->id,
                'title' => $post->title,
                'thumbnail_url' => $postThumbnail,
                'image_count' => $post->postmedias->count(),
            ];
        });

        return response()->json([
            'album' => [
                'id' => $album->id,
                'name' => $album->name,
                'type' => $album->type,
                'thumbnail_url' => $thumbnailUrl,
                'bg_thumbnail_url' => $bgthumbnailUrl,
                'supporters' => $album->supporters->count(),
                'posts' => $posts,
            ]
        ], 200);
    }

    public function album_update(Request $request, $id)
    {
        $album = Auth::user()->albums()->findOrFail($id);
    
        $request->validate([
            'image_type' => 'required|in:profile,cover',
            'image' => 'required|image|max:2048',
        ]);

        $imageType = $request->image_type;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/albums/originals', 's3');
        }
        // Update the appropriate field based on image type
        if ($album->type == 'business') {
            if ($imageType === 'profile') {
                $album->update(['business_logo_original' => $path]);
            } else {
                $album->update(['cover_image_original' => $path]);
            }
        } else {
            if ($imageType === 'profile') {
                $album->update(['thumbnail_original' => $path]);
            } else {
                $album->update(['cover_image_original' => $path]);
            }
        }
        
        return response()->json(['message' => 'Image updated successfully']);
    }
}
