<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Saved;
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

class PostController extends Controller
{

    public function index(Request $request)
    {
        // Get the authenticated user's ID
        $userId = Auth::user()->id;

        // Get the page number from the request (default to 1 if not provided)
        $page = $request->query('page', 1);

        // Get the limit (number of posts per page) from the request (default to 5 if not provided)
        $limit = $request->query('limit', 5);

        // Fetch recommendations for the authenticated user
        $recommendations = Recommendation::where('user_id', $userId)
            ->where('status', 'active') // Filter only active recommendations
            ->orderBy('score', 'desc') // Order by recommendation score (highest first)
            ->paginate($limit, ['*'], 'page', $page);

        // Extract post IDs from the recommendations
        $postIds = $recommendations->pluck('post_id');

        // Fetch posts based on the recommended post IDs
        $posts = Post::with(['postmedias.comments.user', 'postmedias.admires.user', 'album.supporters'])
            ->whereIn('id', $postIds) // Filter posts by the recommended post IDs
            ->where('status', 'active') // Filter only active posts
            ->get();

        // Map posts to the required format
        $postsData = $posts->map(function ($post) {
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

            return [
                'id' => $post->id,
                'user' => $album ? $album->name : 'Unknown Album',
                'supporters' => (string) ($album ? $album->supporters->count() : 0),
                'profile' => $profileUrl, // Profile based on album type
                'description' => $post->description ?: 'No description available provided by the creator',
                'post_media' => $postMediaData,
                'is_verified' => $album ? ($album->is_verified == 1) : false,
            ];
        });

        return response()->json([
            'posts' => $postsData,
            'current_page' => $recommendations->currentPage(),
            'last_page' => $recommendations->lastPage(),
            'total' => $recommendations->total(),
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
            0 // Initial duration, can be updated later
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

    public function storecloud(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:200',
            'type' => 'required',
            'visibility' => 'required|string|in:Public,Private,Friends Only',
            'post_medias' => 'required|array',
            'post_medias.*.file_path' => 'required|string',  // Expecting the file path (e.g., S3 URL)
            'post_medias.*.sequence_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            // Use the 'file_path' that is provided in the request (this will be the S3 path)
            $path = $media['file_path']; // No need to store the image again in S3

            // Create PostMedia without uploading a new file, just save the file path
            PostMedia::create([
                'post_id' => $post->id,
                'file_path' => $path,
                'file_path_compress' => $path,
                'sequence_order' => $media['sequence_order'],
                'status' => 'compressed',
            ]);
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 200);
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
