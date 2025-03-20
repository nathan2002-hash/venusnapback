<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Category;
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

    // Hardcoded ads
    $ads = [
        [
            'id' => 1,
            'image' => 'https://venusnaplondon.s3.eu-west-2.amazonaws.com/uploads/ads/ad1.webp',  // Replace with actual image URL
            'cta_name' => 'Shop Now',
            'cta_link' => 'https://example.com/shop',
            'background_color' => '#FFD700',  // Unique background color for the ad card
            'tag' => 'ad',  // Mark this post as an ad
        ],
        [
            'id' => 2,
            'image' => 'https://venusnaplondon.s3.eu-west-2.amazonaws.com/uploads/ads/ad2.jpeg',  // Replace with actual image URL
            'cta_name' => 'Learn More',
            'cta_link' => 'https://example.com/learn-more',
            'background_color' => '#FF6347',  // Unique background color for the ad card
            'tag' => 'ad',  // Mark this post as an ad
        ]
    ];

    // Merge the hardcoded ads with the posts
    $postsData = $posts->map(function ($post) use ($ads) {
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

        $category = Category::find($post->type);

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

        // Check if this post is an ad by comparing with the hardcoded ads
        $isAd = array_filter($ads, function ($ad) use ($post) {
            return $ad['id'] == $post->id;
        });

        // If it's an ad, we will include the ad details
        if (!empty($isAd)) {
            $ad = reset($isAd);  // Get the first ad (since IDs are unique)

            return [
                'id' => $post->id,
                'album_name' => $album ? $album->name : 'Unknown Album',
                'supporters_count' => (string) ($album ? $album->supporters->count() : 0),
                'profile' => $profileUrl,
                'category' => $category->name,
                'post_media' => $postMediaData,
                'is_verified' => $album ? ($album->is_verified == 1) : false,
                'is_ad' => true,
                'cta_name' => $ad['cta_name'],
                'cta_link' => $ad['cta_link'],
                'background_color' => $ad['background_color'],
                'tag' => $ad['tag'],
            ];
        }

        // If it's not an ad, return the regular post format
        return [
            'id' => $post->id,
            'album_name' => $album ? $album->name : 'Unknown Album',
            'supporters_count' => (string) ($album ? $album->supporters->count() : 0),
            'profile' => $profileUrl,
            'category' => $category->name,
            'post_media' => $postMediaData,
            'is_verified' => $album ? ($album->is_verified == 1) : false,
            'is_ad' => false,  // Indicating this is not an ad
        ];
    });

    return response()->json([
        'posts' => $postsData,
    ], 200);
}

}
