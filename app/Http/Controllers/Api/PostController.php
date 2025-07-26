<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Saved;
use App\Models\Admire;
use App\Models\Report;
use App\Models\Artwork;
use App\Models\Comment;
use App\Models\Category;
use App\Models\PostMedia;
use App\Models\PostState;
use App\Models\AlbumAccess;
use Illuminate\Support\Str;
use App\Models\CommentReply;
use Illuminate\Http\Request;
use App\Jobs\CompressImageJob;
use App\Jobs\LogPostMediaView;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SystemError;

class PostController extends Controller
{

    private function formatDateTimeForUser($dateTime, $timezone = 'Africa/Lusaka')
    {
        if (!$dateTime instanceof \Carbon\Carbon) {
            $dateTime = \Carbon\Carbon::parse($dateTime);
        }

        $dateTime = $dateTime->timezone($timezone);
        $now = now($timezone);

        if ($dateTime->isToday()) {
            return $dateTime->diffForHumans(); // "2 hours ago", "30 minutes ago"
        } elseif ($dateTime->isYesterday()) {
            return 'Yesterday at ' . $dateTime->format('H:i');
        } elseif ($dateTime->diffInDays($now) <= 7) {
            return $dateTime->format('l \a\t H:i'); // "Monday at 14:30"
        } else {
            return $dateTime->format('d M Y, H:i'); // "15 Jun 2023, 14:30"
        }
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = (int)$request->input('limit', 10);

        // Step 1: Get all post IDs the user has seen via post_media_id -> post_id
        $seenPostIds = DB::table('views')
            ->join('post_media', 'views.post_media_id', '=', 'post_media.id')
            ->where('views.user_id', $userId)
            ->pluck('post_media.post_id')
            ->unique()
            ->toArray();

        // Step 2: Prioritize fresh, unseen posts (newest first)
        $freshUnseen = Post::with([
                    'postmedias' => function ($query) use ($userId) {
                        $query->orderBy('sequence_order')
                            ->withCount(['comments', 'admires'])
                            ->with(['comments.user', 'admires.user'])
                            ->withExists(['admires as admired' => function($q) use ($userId) {
                                $q->where('user_id', $userId);
                            }]);
                    },
                    'album.supporters'
                ])
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->whereNotIn('id', $seenPostIds)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        $remaining = $limit - $freshUnseen->count();

        // Step 3: Fill remaining with random unseen posts
        $randomUnseen = collect();
        if ($remaining > 0) {
           $randomUnseen = Post::with([
                    'postmedias' => function ($query) use ($userId) {
                        $query->orderBy('sequence_order')
                            ->withCount(['comments', 'admires'])
                            ->with(['comments.user', 'admires.user'])
                            ->withExists(['admires as admired' => function($q) use ($userId) {
                                $q->where('user_id', $userId);
                            }]);
                    },
                    'album.supporters'
                ])
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->whereNotIn('id', array_merge($seenPostIds, $freshUnseen->pluck('id')->toArray()))
                ->inRandomOrder()
                ->take($remaining)
                ->get();
        }

        // Step 4: Optional fallback to seen posts if feed is still not full
        $posts = $freshUnseen->merge($randomUnseen);
        $stillNeeded = $limit - $posts->count();
        if ($stillNeeded > 0) {
            $seenFillers = Post::with([
                    'postmedias' => function ($query) use ($userId) {
                        $query->orderBy('sequence_order')
                            ->withCount(['comments', 'admires'])
                            ->with(['comments.user', 'admires.user'])
                            ->withExists(['admires as admired' => function($q) use ($userId) {
                                $q->where('user_id', $userId);
                            }]);
                    },
                    'album.supporters'
                ])
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->whereIn('id', $seenPostIds)
                ->inRandomOrder()
                ->take($stillNeeded)
                ->get();

            $posts = $posts->merge($seenFillers);
        }

        $posts = $posts->shuffle()->values();

        if ($posts->count() > 2) {
            // Move first post (most likely repeat) to a random position other than 1st
            $first = $posts->shift(); // Remove the first post
            $insertAt = rand(1, min(4, $posts->count())); // Insert at 2nd to 4th position
            $posts->splice($insertAt, 0, [$first]); // Reinsert it
        }

        // Format the posts data
        $postsData = $posts->map(function ($post) {
            $album = $post->album;
            $profileUrl = asset('default/profile.png'); // Default fallback

            if ($album) {
                if ($album->type == 'personal' || $album->type == 'creator') {
                    $profileUrl = $album->thumbnail_compressed
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : $profileUrl);
                } elseif ($album->type == 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : $profileUrl);
                }
            }
            $viewerTimezone = Auth::check() ? Auth::user()->timezone : 'Africa/Lusaka';

            // Format post media
            $postMediaData = $post->postmedias->map(function ($media) {
                return [
                    'id' => $media->id,
                    'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                    'sequence_order' => (int)$media->sequence_order,
                    'comments_count' => $media->comments->count(),
                    'likes_count' => $media->admires->count(),
                    'admired' => $media->admired,
                    'comments' => $media->comments->map(function ($comment) {
                        return [
                            'id' => $comment->id,
                            'content' => $comment->content,
                            'user' => [
                                'id' => $comment->user->id,
                                'name' => $comment->user->name,
                                'profile_photo_url' => $comment->user->profile_photo_url
                            ]
                        ];
                    }),
                    'admires' => $media->admires->map(function ($admire) {
                        return [
                            'id' => $admire->id,
                            'user' => [
                                'id' => $admire->user->id,
                                'name' => $admire->user->name,
                                'profile_photo_url' => $admire->user->profile_photo_url
                            ]
                        ];
                    })
                ];
            })->values()->all();

            return [
                'id' => $post->id,
                'user_id' => $post->user_id,
                'description' => $post->description ?? 'No description available',
                'album_id' => $album ? (string)$album->id : null,
                'visibility' => $post->visibility,
                'created_at' => $this->formatDateTimeForUser($post->created_at, $viewerTimezone),
                'updated_at' => $post->updated_at,
                'ag_description' => $post->ag_description,
                'status' => $post->status,
                'user' => $album ? $album->name : 'Unknown Album',
                'supporters' => $album ? (string)$album->supporters->count() : "0",
                'album_name' => $album ? (string)$album->name : null,
                'profile' => $profileUrl,
                'album_description' => $album ? ($album->description ?? 'No description available') : null,
                'post_media' => $postMediaData,
                'is_verified' => $album ? ($album->is_verified == 1) : false,
            ];
        })->values()->toArray(); // Ensure we return a proper array without keys

        return response()->json([
            'posts' => $postsData,
            'has_more' => Recommendation::where('user_id', $userId)
                            ->where('status', 'active')
                            ->exists()
        ]);
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

        if (strtolower($post->visibility) === 'private') {
            if (!Auth::check() || Auth::id() != $post->user_id) {
                // Check if user has approved access to the album
                $hasAccess = false;

                if (Auth::check()) {
                    $hasAccess = \DB::table('album_accesses')
                        ->where('user_id', Auth::id())
                        ->where('album_id', $post->album_id)
                        ->where('status', 'approved')
                        ->exists();
                }

                if (!$hasAccess) {
                    return response()->json(['error' => 'Post not found'], 404);
                }
            }
        }


        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $album = $post->album;
        $viewerTimezone = Auth::check() ? Auth::user()->timezone : 'Africa/Lusaka';

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
            $ipaddress,
            $request->header('User-Agent'),
            $request->header('Device-Info'),
            6, // Initial duration, can be updated later
            true // clicked = true
        );

        // Sort post media by sequence_order before mapping
        $sortedMedia = $post->postMedias->sortBy('sequence_order');

        // Transform post media data
        $postMediaData = $sortedMedia->map(function ($media) {
            return [
                'id' => $media->id,
                'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                'sequence_order' => $media->sequence_order,
                'comments_count' => $media->comments->count(),
                'likes_count' => $media->admires->count(),
            ];
        })->values()->toArray(); // Use values() to reset array keys after sorting

        return response()->json([
            'id' => $post->id,
            'user' => $album ? $album->name : 'Unknown Album',
            'supporters' => (string) ($album ? $album->supporters->count() : 0),
            'created_at' => $this->formatDateTimeForUser($post->created_at, $viewerTimezone),
            'album_id' => (string) $album->id,
            'album_name' => (string) $album->name,
            'profile' => $profileUrl ?? asset('default/profile.png'), // Fallback if no album
            'description' => $post->description ?: 'No description available provided by the creator',
            'album_description' => $album->description ?? 'No description available provided by the creator',
            'post_media' => $postMediaData,
            'is_verified' => $album ? ($album->is_verified == 1) : false,
        ], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user(); // Get authenticated user
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Create the post
        $post = new Post();
        $post->user_id = Auth::user()->id; // Assign authenticated user's ID
        $post->description = $request->description;
        // Randomly select type and category from the arrays
        $post->status = 'review';
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
        return response()->json([
            'id' => $post->id,
            'description' => $post->description,
            'album_id' => $post->album_id,
            'album' => $post->album->name,
            'visibility' => $post->visibility,
            'post_media' => $post->postmedias->map(function($media) {
                return [
                    'id' => $media->id,
                    'file_path' => Storage::disk('s3')->url($media->file_path_compress), // Using the accessor we defined
                    'sequence_order' => $media->sequence_order
                ];
            }),
            'can_edit' => Auth::id() === optional($post->album)->user_id,
        ]);
    }

    public function postdelete(Request $request, $id)
    {
        $user = Auth::user(); // Assuming you're using Laravel auth

        $post = Post::findOrFail($id);

        // Check if the user owns the album or has been granted access
        $albumId = $post->album_id;

        $hasAccess = DB::table('album_accesses')
            ->where('album_id', $albumId)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();

        // Optionally allow post owner or admin to delete without access entry
        $isOwner = $post->user_id == $user->id;

        if (!($hasAccess || $isOwner)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();

        // Proceed with deletion request
        $post->status = 'deletion';
        $post->save();

        $poststate = new PostState();
        $poststate->user_id = $user->id;
        $poststate->post_id = $post->id;
        $poststate->title = "Post Deletion Request";
        $poststate->initiator = $isOwner ? 'owner' : 'shared_user';
        $poststate->reason = 'Post deletion requested by user';
        $poststate->meta = [
            'description' => $post->description,
            'album_id' => $post->album_id,
            'visibility' => $post->visibility,
            'ip_address' => $realIp,
            'user_agent' => $request->header('User-Agent') ?? 'unknown',
            'device_info' => $request->header('Device-Info') ?? 'unknown',
        ];
        $poststate->state = 'deletion';
        $poststate->save();

        return response()->json([
            'id' => $post->id,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $post = Post::with('album')->findOrFail($id);
        $userId = Auth::user()->id;

        // Check if the user owns the album
        $isOwner = $post->album->user_id == $userId;

        // Check if the user has access to the album
        $hasAccess = AlbumAccess::where('album_id', $post->album_id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->exists();

        if (!($isOwner || $hasAccess)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Update post details
        $post->update([
            'description' => $request->description,
            'status' => 'review',
            'album_id' => $request->album_id,
            'visibility' => $request->visibility,
        ]);

        // Handle media deletions
        $mediaToDelete = $request->media_to_delete ?? [];
        if (!empty($mediaToDelete)) {
            PostMedia::whereIn('id', $mediaToDelete)
                ->where('post_id', $post->id)
                ->delete();
        }

        // Update sequence orders for existing media and log old/new values
        $sequenceLog = [];
        if ($request->existing_media) {
            foreach ($request->existing_media as $mediaId => $newOrder) {
                $media = PostMedia::where('id', $mediaId)
                    ->where('post_id', $post->id)
                    ->first();

                if ($media && $media->sequence_order != $newOrder) {
                    $sequenceLog[] = [
                        'media_id' => $media->id,
                        'old_sequence_order' => $media->sequence_order,
                        'new_sequence_order' => $newOrder,
                    ];

                    $media->sequence_order = $newOrder;
                    $media->save();
                }
            }
        }

        // Handle new media uploads
        $newMediaCount = 0;
        if ($request->hasFile('post_medias')) {
            foreach ($request->file('post_medias') as $index => $file) {
                // Get the corresponding sequence order
                $sequenceOrder = $request->post_medias_orders[$index] ?? ($index + 1);

                $path = $file->store('uploads/posts/originals', 's3');

                $postMedia = PostMedia::create([
                    'post_id' => $post->id,
                    'file_path' => $path,
                    'sequence_order' => $sequenceOrder,
                    'status' => 'original',
                ]);

                CompressImageJob::dispatch($postMedia->fresh());
                $newMediaCount++;
            }
        }

        // Log the change in post_states
        $postState = new PostState();
        $postState->user_id = $userId;
        $postState->post_id = $post->id;
        $postState->title = $post->title ?? 'Post Update';
        $postState->initiator = $isOwner ? 'owner' : 'shared_user';
        $postState->reason = $request->reason ?? 'Post content edited';
        $postState->state = 'edit';
        $postState->meta = [
            'description' => $request->description,
            'type' => $request->type,
            'album_id' => $request->album_id,
            'visibility' => $request->visibility,
            'sequence_log' => $sequenceLog,
            'media_deleted' => $mediaToDelete,
            'new_media_count' => $newMediaCount,
        ];
        $postState->save();

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post->refresh()->load(['postmedias', 'album']),
            'sequence_changes' => $sequenceLog,
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
            SystemError::create([
                'user_id' => $user->id ?? null,
                'context' => 'post_creation',
                'message' => 'Post creation failed: ' . $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'metadata' => json_encode([
                    'user_id' => $user->id ?? null,
                    'post_id' => $post->id,
                ]),
            ]);
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
            ->where('status', 'active') // Only include active posts
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
