<?php

namespace App\Http\Controllers\Api;

use App\Models\Album;
use App\Jobs\AlbumCreate;
use App\Models\AlbumView;
use App\Models\AlbumAccess;
use App\Models\AlbumCategory;
use App\Models\Admire;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\CreateNotificationJob;
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
        $user = Auth::user();

        // Owned albums (Eloquent collection)
        $ownedAlbums = Album::where('user_id', $user->id)
            ->whereIn('type', ['creator', 'business', 'personal'])
            ->select('id', 'name', 'type')
            ->get()
            ->map(function ($album) {
                $typeLabel = match($album->type) {
                    'personal' => 'Personal',
                    'creator' => 'Creator',
                    default => 'Business',
                };

                return [
                    'id' => $album->id,
                    'album_name' => "{$album->name} ($typeLabel)",
                ];
            });

        // Shared albums (direct array conversion)
        $sharedAlbums = DB::table('album_accesses')
            ->join('albums', 'album_accesses.album_id', '=', 'albums.id')
            ->where('album_accesses.user_id', $user->id)
            ->where('album_accesses.status', 'approved')
            ->whereIn('albums.type', ['creator', 'business'])
            ->select('albums.id', 'albums.name', 'albums.type')
            ->get()
            ->map(function ($album) {
                $typeLabel = match($album->type) {
                    'creator' => 'Creator',
                    default => 'Business',
                };

                return [
                    'id' => $album->id,
                    'album_name' => "{$album->name} ($typeLabel)",
                ];
            });

        // Merge both collections
        $albums = $ownedAlbums->merge($sharedAlbums);

        return response()->json([
            'success' => true,
            'data' => $albums->values() // This ensures sequential array keys
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
        $album->status = 'active';
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

    public function creatornamecheck(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $name = $request->input('name');

        // Check existing albums (case insensitive)
        if (Album::whereRaw('LOWER(name) = LOWER(?)', [$name])->exists()) {
            return response()->json([
                'available' => false,
                'message' => 'This album name is already taken.'
            ], 200);
        }

        return response()->json([
            'available' => true,
            'message' => 'This name is available.'
        ], 200);
    }

    public function checkGeneralName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'exclude_id' => 'nullable|integer'
        ]);

        $name = $request->name;
        $excludeId = $request->exclude_id;

        // First check reserved names in database (case insensitive)
        $isReserved = DB::table('reserved_names')
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();

        if ($isReserved) {
            return response()->json([
                'available' => false,
                'message' => 'This name is reserved and cannot be used.',
                'is_reserved' => true
            ], 200);
        }

        // Then check existing albums (case insensitive)
        $query = Album::whereRaw('LOWER(name) = LOWER(?)', [$name]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            return response()->json([
                'available' => false,
                'message' => 'This album name is already taken.',
                'is_reserved' => false
            ], 200);
        }

        return response()->json([
            'available' => true,
            'message' => 'This name is available.'
        ], 200);
    }

    public function creatorstore(Request $request)
    {
        $name = $request->name;

        // Check existing albums (case insensitive)
        if (Album::whereRaw('LOWER(name) = LOWER(?)', [$name])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This album name is already taken choose another name.'
            ], 422);
        }

        $album = new Album();
        $album->user_id = Auth::user()->id;
        $album->type = 'creator';
        $album->status = 'active';
        $album->name = $request->name;
        $album->album_category_id = $request->content_type;
        $album->content_type = $request->content_type;
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

    public function businessnamecheck(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $name = $request->input('name');

        // Check reserved names in database (case insensitive)
        $isReserved = DB::table('reserved_names')
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();

        if ($isReserved) {
            return response()->json([
                'available' => false,
                'message' => 'This name is reserved and cannot be used if you own this business please contact us.'
            ], 200);
        }

        // Check existing albums (case insensitive)
        if (Album::whereRaw('LOWER(name) = LOWER(?)', [$name])->exists()) {
            return response()->json([
                'available' => false,
                'message' => 'This name is already taken please choose another name.'
            ], 200);
        }

        return response()->json([
            'available' => true,
            'message' => 'This name is available.'
        ], 200);
    }


    public function businessstore(Request $request)
    {
        $name = $request->name;

        // Check reserved names in database (case insensitive)
        $isReserved = DB::table('reserved_names')
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->exists();

        if ($isReserved) {
            return response()->json([
                'success' => false,
                'message' => 'This name is reserved and cannot be used if you own this business please contact us.'
            ], 422);
        }

        // Check existing albums (case insensitive)
        if (Album::whereRaw('LOWER(name) = LOWER(?)', [$name])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This album name is already taken choose another name.'
            ], 422);
        }

        // Proceed with album creation if name checks pass
        $album = new Album();
        $album->user_id = Auth::user()->id;
        $album->type = 'business';
        $album->status = 'active';
        $album->visibility = 'public';
        $album->name = $name;
        $album->description = $request->description;
        $album->business_category = $request->business_category;
        $album->album_category_id = $request->business_category;

        // Fix the is_paid_access assignment (was checking wrong object)
        $album->is_paid_access = $request->is_paid_access ? 1 : 0;

        $album->phone = $request->phone;
        $album->email = $request->email;
        $album->location = $request->location;
        $album->website = $request->website;
        $album->facebook = $request->facebook;
        $album->linkedin = $request->linkedin;

        if ($request->hasFile('business_logo')) {
            $bpath = $request->file('business_logo')->store('uploads/albums/originals', 's3');
            $album->business_logo_original = $bpath;
        }

        if ($request->hasFile('cover_image')) {
            $cpath = $request->file('cover_image')->store('uploads/albums/originals', 's3');
            $album->cover_image_original = $cpath;
        }

        $album->save();

        AlbumCreate::dispatch($album->id);

        return response()->json([
            'success' => true,
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
        $userId = $request->user()->id;

        // 1. Albums the user owns (only active)
        $ownedAlbums = Album::where('user_id', $userId)
            ->where('status', 'active')
            ->select(
                'id', 'user_id', 'name', 'description', 'thumbnail_original',
                'business_logo_original', 'business_logo_compressed',
                'thumbnail_compressed', 'type', 'is_verified', 'created_at'
            )
            ->get();

        // 2. Albums the user has been granted access to (approved & active only)
        $accessedAlbums = Album::whereIn('id', function ($query) use ($userId) {
                $query->select('album_id')
                    ->from('album_accesses')
                    ->where('user_id', $userId)
                    ->where('status', 'approved');
            })
            ->where('status', 'active')
            ->select(
                'id', 'user_id', 'name', 'description', 'thumbnail_original',
                'business_logo_original', 'business_logo_compressed',
                'thumbnail_compressed', 'type', 'is_verified', 'created_at'
            )
            ->get();

        // 3. Merge and remove duplicates by album ID
        $allAlbums = $ownedAlbums->merge($accessedAlbums)->unique('id');

        // 4. Map album info with thumbnails, etc.
        $albums = $allAlbums->map(function ($album) {
            $thumbnailUrl = null;

            if ($album->type === 'personal' || $album->type === 'creator') {
                $thumbnailUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : null);
            } elseif ($album->type === 'business') {
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
                'is_verified' => (bool) $album->is_verified,
                'supporters' => $album->supporters->count(),
                'posts' => $album->posts->count(),
                'thumbnail_url' => $thumbnailUrl,
                'created_at' => $album->created_at->format('l, d F Y at h:i A'),
            ];
        });

        return response()->json(['albums' => $albums->values()]);
    }


    public function show($albumId, Request $request)
    {
        $album = Album::with(['posts.postmedias'])->find($albumId);
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        if (!$album) {
            return response()->json([
                'message' => 'Album not found'
            ], 404);
        }

        $user = Auth::user();
        $ip = $realIp;
        $userAgent = request()->header('User-Agent');

         // Optional: prevent duplicate views in short span
         $alreadyViewed = AlbumView::where('album_id', $albumId)
             ->where(function ($query) use ($user, $ip) {
                 if ($user) {
                     $query->where('user_id', $user->id);
                 } else {
                     $query->where('ip_address', $ip);
                 }
             })
             ->where('created_at', '>=', now()->subMinutes(30)) // 30 minutes gap
             ->exists();

         if (!$alreadyViewed) {
             AlbumView::create([
                 'album_id' => $album->id,
                 'user_id' => $user?->id,
                 'ip_address' => $ip,
                 'user_agent' => $userAgent,
             ]);
         }

         $receiver = $album->user_id;

         if ($receiver !== $user->id) {
            CreateNotificationJob::dispatch(
                $user,
                $album,
                'viewed_album',
                $receiver,
                [
                    'viewer' => $user->id,
                    'album_id' => $album->id
                ]
            );
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

       $posts = $album->posts
        ->where('status', 'active')
        ->values() // This resets keys to 0-based index
        ->map(function ($post) {
            $postThumbnail = $post->postmedias->first()
                ? Storage::disk('s3')->url($post->postmedias->first()->file_path_compress)
                : null;

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
                'is_verified' => (bool)$album->is_verified,
                'bg_thumbnail_url' => $bgthumbnailUrl,
                'supporters' => $album->supporters->count(),
                'posts' => $posts,
            ]
        ], 200);
    }

    public function showviewer($albumId, Request $request)
    {
        $user = Auth::user();

        $album = Album::with(['posts.postmedias', 'supporters'])
        ->where('id', $albumId)
        ->where(function ($query) use ($user) {
            $query->where('visibility', 'public'); // Allow if public
            if ($user) {
                $query->orWhere(function ($q) use ($user) {
                    $q->where('user_id', $user->id); // Allow if owner
                });

                $query->orWhereIn('id', function ($subQuery) use ($user) {
                    $subQuery->select('album_id')
                        ->from('album_accesses')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved'); // Allow if approved access
                });
            }
        })
        ->first();


        if (!$album) {
            return response()->json([
                'message' => 'Album not found'
            ], 404);
        }

        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        if ($user) {
            $alreadyViewed = AlbumView::where('album_id', $albumId)
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHours(24)) // One view every 24 hrs
                ->exists();

            if (!$alreadyViewed) {
                AlbumView::create([
                    'album_id' => $album->id,
                    'user_id' => $user->id,
                    'ip_address' => $realIp,
                    'user_agent' => $request->header('User-Agent'),
                ]);
            }
        }


         $receiver = $album->user_id;

        if ($receiver !== $user->id) {
            CreateNotificationJob::dispatch(
                $user,
                $album,
                'viewed_album',
                $receiver,
                [
                    'username' => $user->name,
                    'album_id' => $album->id,
                    'album_name' => $album->name,
                    'viewer_id' => $user->id
                ]
            );
        }
        $isSupporter = $user ? $album->supporters()->where('user_id', Auth::user()->id)->exists() : false;



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
            $thumbnailUrl = asset('defaults/album.png');
        }

        $bgthumbnailUrl = $album->cover_image_compressed
            ? Storage::disk('s3')->url($album->cover_image_compressed)
            : ($album->cover_image_original
                ? Storage::disk('s3')->url($album->cover_image_original)
                : null);

        $posts = $album->posts
        ->where('status', 'active')
        ->values() // This resets keys to 0-based index
        ->map(function ($post) {
            $postThumbnail = $post->postmedias->first()
                ? Storage::disk('s3')->url($post->postmedias->first()->file_path_compress)
                : null;

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
                'description' => $album->description,
                'is_verified' => (bool)$album->is_verified,
                'thumbnail_url' => $thumbnailUrl,
                'bg_thumbnail_url' => $bgthumbnailUrl,
                'supporters' => $album->supporters->count(),
                'posts' => $posts,
                'email' => in_array($album->type, ['creator', 'business']) ? $album->email : null,
                'phone' => in_array($album->type, ['creator', 'business']) ? $album->phone : null,
                'is_supporter' => $isSupporter, // Add this line
                'website' => $album->website,
                'facebook' => $album->facebook,
                'linkedin' => $album->linkedin,
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

        AlbumCreate::dispatch($album->id);

        return response()->json(['message' => 'Image updated successfully']);
    }

    public function albumAnalytics($id)
    {
        $album = Album::find($id);

        $albumId = $id;

        if (!$album) {
            return response()->json(['message' => 'Album not found'], 404);
        }

        // Get all post IDs in this album
        $postIds = Post::where('album_id', $albumId)->pluck('id');

        // Get all post_media_ids from these posts
        $postMediaIds = PostMedia::whereIn('post_id', $postIds)->pluck('id');

        // Album views summary
        $albumViewsCount = AlbumView::where('album_id', $albumId)->count();
        $uniqueViewers = AlbumView::where('album_id', $albumId)->distinct('ip_address')->count('ip_address');

        // Media views summary
        $mediaViewsCount = View::whereIn('post_media_id', $postMediaIds)->count();

        // Total duration watched across all medias
        $totalDuration = View::whereIn('post_media_id', $postMediaIds)->sum(DB::raw("CAST(duration AS UNSIGNED)"));

        // Admires (likes) summary
        $admiresCount = Admire::whereIn('post_media_id', $postMediaIds)->count();

        // Post and media counts
        $postCount = $postIds->count();
        $mediaCount = $postMediaIds->count();

        // Optional: Top viewed media
        $topMedia = View::whereIn('post_media_id', $postMediaIds)
            ->select('post_media_id', DB::raw('COUNT(*) as views'))
            ->groupBy('post_media_id')
            ->orderByDesc('views')
            ->first();

        return response()->json([
            'album_id' => $albumId,
            'album_name' => $album->name,
            'views' => [
                'total_album_views' => $albumViewsCount,
                'unique_viewers' => $uniqueViewers,
                'total_media_views' => $mediaViewsCount,
            ],
            'engagement' => [
                'admires' => $admiresCount,
                'total_duration_seconds' => (int) $totalDuration,
            ],
            'content' => [
                'post_count' => $postCount,
                'media_count' => $mediaCount,
            ],
            'top_media' => $topMedia ? [
                'media_id' => $topMedia->post_media_id,
                'views' => $topMedia->views,
            ] : null
        ]);
    }
}
