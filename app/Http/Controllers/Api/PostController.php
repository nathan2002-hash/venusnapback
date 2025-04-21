<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Saved;
use App\Models\Category;
use App\Models\Admire;
use App\Models\Report;
use App\Models\Comment;
use App\Models\PostMedia;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Jobs\CompressImageJob;
use App\Jobs\LogPostMediaView;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Artwork;

class PostController extends Controller
{

public function index(Request $request)
{
    $userId = Auth::user()->id;
    $limit = $request->query('limit', 3); // Default to 3 posts per fetch

    // Get available active recommendations
    $recommendations = Recommendation::where('user_id', $userId)
        ->where('status', 'active')
        ->inRandomOrder()
        ->take($limit)
        ->get();

    // If not enough active recommendations, recycle some fetched ones
    if ($recommendations->count() < $limit) {
        $needed = $limit - $recommendations->count();
        
        Recommendation::where('user_id', $userId)
            ->where('status', 'fetched')
            ->orderBy('updated_at', 'asc') // Oldest first
            ->limit($needed)
            ->update(['status' => 'active']);
            
        // Get the newly activated recommendations
        $additionalRecs = Recommendation::where('user_id', $userId)
            ->where('status', 'active')
            ->inRandomOrder()
            ->take($needed)
            ->get();
            
        $recommendations = $recommendations->merge($additionalRecs);
    }

    // Mark these as fetched
    Recommendation::whereIn('id', $recommendations->pluck('id'))
        ->update(['status' => 'fetched']);

    // Get the posts
    $posts = Post::with(['postmedias.comments.user', 'postmedias.admires.user', 'album.supporters'])
        ->whereIn('id', $recommendations->pluck('post_id'))
        ->where('status', 'active')
        ->get();
    $postsData = $posts->map(function ($post) {
        $album = $post->album;

        if ($album) {
            if ($album->type == 'personal' || $album->type == 'creator') {
                $profileUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : asset('default/profile.png'));
            } elseif ($album->type == 'business') {
                $profileUrl = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : asset('default/profile.png'));
            }
        }

        $postMediaData = $post->postMedias->map(function ($media) {
            return [
                'id' => $media->id,
                'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                'sequence_order' => $media->sequence_order,
                'comments_count' => $media->comments->count(),
                'likes_count' => $media->admires->count(),
            ];
        })->values()->toArray();

        return [
            'id' => $post->id,
            'user' => $album ? $album->name : 'Unknown Album',
            'supporters' => (string) ($album ? $album->supporters->count() : 0),
            'album_id' => (string) $album->id,
            'profile' => $profileUrl,
            'description' => $post->description ?: 'No description available provided by the creator',
            'post_media' => $postMediaData,
            'is_verified' => $album ? ($album->is_verified == 1) : false,
        ];
    });

    return response()->json([
        'posts' => $postsData,
        'total_available' => $postsData->count(),
    ], 200);
}

    public function show(Request $request, $id)
    {
        $post = Post::with(['postmedias.comments.user', 'postmedias.admires.user', 'album.supporters'])
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $album = $post->album;

        if ($album) {
            if ($album->type == 'personal' || $album->type == 'creator') {
                // Personal and Creator albums
                $profileUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : asset('default/profile.png'));
            } elseif ($album->type == 'business') {
                // Business albums use business logo
                $profileUrl = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : asset('default/profile.png'));
            }
        }
        LogPostMediaView::dispatch(
            $id,
            Auth::user()->id,
            $request->ip(),
            $request->header('User-Agent'),
            $request->header('Device-Info'),
            6 // Initial duration, can be updated later
        );
        // Transform post media data
        $postMediaData = $post->postMedias->map(function ($media) {
            return [
                'id' => $media->id,
                'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                'sequence_order' => $media->sequence_order,
                'comments_count' => $media->comments->count(),
                'likes_count' => $media->admires->count(),
            ];
        })->toArray();

        return response()->json([
            'id' => $post->id,
            'user' => $album ? $album->name : 'Unknown Album',
            'supporters' => (string) ($album ? $album->supporters->count() : 0),
            'album_id' => (string) $album->id,
            'profile' => $profileUrl, // Profile based on album type
            'description' => $post->description ?: 'No description available provided by the creator',
            'post_media' => $postMediaData,
            'is_verified' => $album ? ($album->is_verified == 1) : false,
        ], 200);
    }



    public function store(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // $validator = Validator::make($request->all(), [
        //     'description' => 'required|string|max:200',
        //     'type' => 'required',
        //     'visibility' => 'required|string|in:Public,Private,Friends Only',
        //     'post_medias' => 'required|array',
        //     'post_medias.*.file' => 'required|file|mimes:jpeg,png,jpg,webp|max:2048',
        //     'post_medias.*.sequence_order' => 'required|integer',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        // Create the post
        $post = new Post();
        $post->user_id = Auth::user()->id; // Assign authenticated user's ID
        $post->description = $request->description;
        $post->type = $request->type;
        $post->album_id = $request->album_id;
        $post->visibility = $request->visibility;
        $post->save();

        $sequenceOrders = collect($request->post_medias)
            ->sortBy('sequence_order') // Ensure images are sorted by their sequence order
            ->values(); // Reindex the collection

            foreach ($sequenceOrders as $media) {
                $path = $media['file']->store('uploads/posts/originals', 's3');  // Store the original file

                $postMedia = PostMedia::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'sequence_order' => $media['sequence_order'],
                    'status' => 'original',  // Status set to original
                ]);

                // Dispatch a job to compress the image asynchronously
                CompressImageJob::dispatch($postMedia->fresh());  // Use a queued job for compression
            }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 200);
    }

    public function postedit($id)
    {
        $post = Post::with(['postmedias', 'album'])
                    ->findOrFail($id);
        $category = Category::find($post->type);
    
        return response()->json([
            'id' => $post->id,
            'description' => $post->description,
            'type' => $category->name,
            'album_id' => $post->album_id,
            'album' => $post->album->name,
            'visibility' => $post->visibility,
            'post_media' => $post->postmedias->map(function($media) {
                return [
                    'id' => $media->id,
                    'file_path' => Storage::disk('s3')->url($media->file_path), // Using the accessor we defined
                    'sequence_order' => $media->sequence_order
                ];
            }),
            'can_edit' => Auth::id() === optional($post->album)->user_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        
        // Authorization check
        if ($post->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Update post details
        $post->update([
            'description' => $request->description,
            'type' => $request->type,
            'album_id' => $request->album_id,
            'visibility' => $request->visibility
        ]);
    
        // Handle media deletions
        if ($request->media_to_delete) {
            PostMedia::whereIn('id', $request->media_to_delete)
                    ->where('post_id', $post->id)
                    ->delete();
        }
    
        // Update sequence orders for existing media
        if ($request->existing_media) {
            foreach ($request->existing_media as $mediaId => $sequenceOrder) {
                PostMedia::where('id', $mediaId)
                        ->where('post_id', $post->id)
                        ->update(['sequence_order' => $sequenceOrder]);
            }
        }
    
        // Handle new media
        if ($request->hasFile('post_medias')) {
            foreach ($request->post_medias as $media) {
                $path = $media['file']->store('uploads/posts/originals', 's3');
                
                PostMedia::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'sequence_order' => $media['sequence_order'],
                    'status' => 'original'
                ]);
            }
        }
    
        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->load('postMedia')
        ]);
    }

    public function storecloud(Request $request)
{
    $user = Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    DB::beginTransaction();
    try {
        // Create the post
        $post = new Post();
        $post->user_id = $user->id;
        $post->description = $request->description;
        $post->type = $request->type;
        $post->status = 'active';
        $post->album_id = $request->album_id;
        $post->visibility = $request->visibility;
        $post->save();

        // Get all artwork IDs from the request
        $artworkIds = collect($request->post_medias)->pluck('artwork_id');

        // Fetch all artworks at once for efficiency
        $artworks = Artwork::whereIn('id', $artworkIds)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('id');

        // Create post media entries in the order specified by Flutter
        foreach ($request->post_medias as $media) {
            $artwork = $artworks->get($media['artwork_id']);

            if (!$artwork) {
                throw new \Exception("Artwork not found or doesn't belong to user");
            }

            PostMedia::create([
                'post_id' => $post->id,
                'file_path' => $artwork->file_path,
                'file_path_compress' => $artwork->thumbnail,
                'sequence_order' => $media['sequence_order'], // Use the sequence from Flutter
                'status' => 'compressed',
            ]);
        }

        DB::commit();

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Post creation failed: ' . $e->getMessage());
        return response()->json(['message' => 'Post creation failed: ' . $e->getMessage()], 500);
    }
}



    public function save(Request $request, $id)
    {
        $save = new Saved();
        $save->post_id = $id;
        $save->user_id = '2';
        $save->save();
    }


    public function report(Request $request, $id)
    {
        $report = new Report();
        $report->apost_media_id = $id;
        $report->user_id = '2';
        $report->status = 'pending';
        $report->reason = $request->report;
        $report->save();
    }

    public function getRecentPosts(Request $request) {
        $user = $request->user();
        $posts = $user->posts()->with('postMedias')->latest()->take(6)->get();
        return response()->json(['posts' => $posts]);
    }


    public function getPosts(Request $request) {
        $user = $request->user();
        $perPage = 6;
        $page = $request->query('page', 1);

        $posts = $user->posts()
            ->with(['postMedias', 'album']) // Load album details
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'posts' => $posts->map(function ($post) {
                $album = $post->album;
                $thumbnailUrl = null;

                if ($album) {
                    if ($album->type == 'personal' || $album->type == 'creator') {
                        // For personal and creator albums
                        $thumbnailUrl = $album->thumbnail_compressed
                            ? Storage::disk('s3')->url($album->thumbnail_compressed)
                            : ($album->thumbnail_original
                                ? Storage::disk('s3')->url($album->thumbnail_original)
                                : null);
                    } elseif ($album->type == 'business') {
                        // For business albums, use business logo thumbnail if available
                        $thumbnailUrl = $album->business_logo_compressed
                            ? Storage::disk('s3')->url($album->business_logo_compressed)
                            : ($album->business_logo_original
                                ? Storage::disk('s3')->url($album->business_logo_original)
                                : null);
                    }
                }

                return [
                    'id' => $post->id,
                    'description' => $post->description,
                    'album' => $album ? $album->name : null,
                    'album_type' => $album ? $album->type : null,
                    'album_thumbnail' => $thumbnailUrl,
                    'created_at' => $post->created_at->format('d M Y, h:i A'),
                    'postMedias' => $post->postMedias->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'media_url' => Storage::disk('s3')->url($media->file_path),
                            'media_url_compress' => Storage::disk('s3')->url($media->file_path_compress),
                            'sequence_order' => $media->sequence_order,
                        ];
                    }),
                ];
            }),
            'has_more' => $posts->hasMorePages(),
        ]);
    }
}
