<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostExploreController extends Controller
{
    public function index(Request $request)
{
    // Get authenticated user's ID
    $userId = Auth::id();

    // Get pagination parameters
    $page = $request->query('page', 1);
    $limit = $request->query('limit', 5);
    // Fetch posts with their media, album, and engagement data
    $posts = Post::with([
        'postmedias' => function ($query) {
            $query->orderBy('sequence_order', 'asc');
        },
        'postmedias.comments.user',
        'postmedias.admires.user',
        'album.supporters',
    ])
    ->where('status', 'active')
    ->get();

    // Map posts to the required format
    $postsData = $posts->map(function ($post) {
        $album = $post->album;

        // Set default profile picture
        $defaultProfile = asset('default/profile.png');
        $profileUrl = $defaultProfile;

        if ($album) {
            if ($album->type == 'personal' || $album->type == 'creator') {
                $profileUrl = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : $defaultProfile);
            } elseif ($album->type == 'business') {
                $profileUrl = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : $defaultProfile);
            }
        }

        // Transform post media
        $postMediaData = $post->postmedias->map(function ($media) {
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
            'album_name' => $album ? $album->name : 'Unknown Album',
            'supporters_count' => (string) ($album ? $album->supporters->count() : 0),
            'profile' => $profileUrl,
            'description' => $post->description ?: 'No description provided',
            'post_media' => $postMediaData,
            'is_verified' => $album ? ($album->is_verified == 1) : false,
        ];
    });

    return response()->json([
        'posts' => $postsData,
    ], 200);
}

}
