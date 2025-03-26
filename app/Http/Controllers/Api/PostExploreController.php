<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use App\Models\Post;
use App\Models\Adboard;
use App\Jobs\AdClickJob;
use App\Models\Category;
use App\Models\AdSession;
use App\Models\AdImpression;
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
        $limit = $request->query('limit', 12);

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

        // Fetch ads from the database
        $ads = Ad::where('status', 'published')
        ->whereHas('adboard', function ($query) {
            $query->where('points', '>', 0);
        })
        ->get();

        // Transform posts
        $combined = $posts->map(function ($post) {
            $album = $post->album;
            $defaultProfile = asset('default/profile.png');
            $profileUrl = $defaultProfile;

            if ($album) {
                if (in_array($album->type, ['personal', 'creator'])) {
                    $profileUrl = $album->thumbnail_compressed
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : $defaultProfile);
                } elseif ($album->type === 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : $defaultProfile);
                }
            }

            $category = isset($post->type) ? Category::find($post->type) : null;
            $categoryName = $category ? $category->name : 'Unknown Category';



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

            // Return the post data
            return [
                'id' => $post->id,
                'album_name' => $album ? $album->name : 'Unknown Album',
                'supporters_count' => (string) ($album ? $album->supporters->count() : 0),
                'profile' => $profileUrl,
                'category' => $categoryName,
                'post_media' => $postMediaData,
                'is_verified' => $album ? ($album->is_verified == 1) : false,
                'is_ad' => false,  // Indicating this is not an ad
            ];
        });

        // Transform ads from the database
        $adsData = $ads->map(function ($ad) {
            $album = $ad->adboard->album ?? null;
            $defaultProfile = asset('default/profile.png');

            $profileUrl = $defaultProfile;

            if ($album) {
                if (in_array($album->type, ['personal', 'creator'])) {
                    $profileUrl = $album->thumbnail_compressed
                        ? Storage::disk('s3')->url($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? Storage::disk('s3')->url($album->thumbnail_original)
                            : $defaultProfile);
                } elseif ($album->type === 'business') {
                    $profileUrl = $album->business_logo_compressed
                        ? Storage::disk('s3')->url($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? Storage::disk('s3')->url($album->business_logo_original)
                            : $defaultProfile);
                }
            }

            return [
                'id' => $ad->id,
                'album_name' => $album ? $album->name : 'Unknown Album',
                'supporters_count' => '0',  // Ads won't have supporters count
                'profile' => $profileUrl,
                'category' => 'Advertisement',  // Category for ads
                'is_ad' => true,  // Indicating this is an ad
                'cta_name' => $ad->cta_name,
                'cta_link' => $ad->cta_link,
                'background_color' => '#FFD700', // Default ad background color
                'tag' => 'ad',
                'post_media' => $ad->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'filepath' => Storage::disk('s3')->url($media->file_path),
                        'sequence_order' => $media->sequence_order,
                    ];
                })->toArray(),
                'is_verified' => false,  // Ads are not verified
            ];
        });

        // Merge posts and ads together and shuffle them
        $combined = $combined->merge($adsData)->shuffle();

        // Paginate the results if needed
        $paginatedResults = $combined->forPage($page, $limit);

        return response()->json([
            'posts' => $paginatedResults,
        ], 200);
    }

    public function getAdById(Request $request, $id)
    {
        // Fetch the ad from the database
        $ad = Ad::find($id);

        // Check if ad exists
        if (!$ad) {
            return response()->json(['error' => 'Ad not found'], 404);
        }

        // Prepare media URLs
        $mediaUrls = $ad->media->map(function ($media) {
            return Storage::disk('s3')->url($media->file_path);
        });

        $album = $ad->adboard->album ?? null;

        // Determine profile image
        $defaultProfile = asset('default/profile.png');
        $profileImage = $defaultProfile;

        if ($album) {
            if ($album->type == 'personal' || $album->type == 'creator') {
                $profileImage = $album->thumbnail_compressed
                    ? Storage::disk('s3')->url($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? Storage::disk('s3')->url($album->thumbnail_original)
                        : $defaultProfile);
            } elseif ($album->type == 'business') {
                $profileImage = $album->business_logo_compressed
                    ? Storage::disk('s3')->url($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? Storage::disk('s3')->url($album->business_logo_original)
                        : $defaultProfile);
            }
        }

        AdClickJob::dispatch(
            $ad->id,
            $request->ip(),
            $request->header('Device-Info'),
            $request->header('User-Agent'),
            Auth::user()->id
        );

        // Construct response
        return response()->json([
            'media_urls' => $mediaUrls,
            'creator' => [
                'name' => $album->name,
                'profile_image' => $profileImage,
                'is_verified' => (bool) ($album ? $album->is_verified : false),
            ],
            'supporters' => $album ? $album->supporters->count() : 0,
            'cta_name' => $ad->cta_name,
            'cta_link' => $ad->cta_link,
        ], 200);
    }

    public function sendAdSeenRequest(Request $request)
    {
        $ad = Ad::find($request->ad_id);

        $adboard = Adboard::find($ad->adboard_id);
        if (!$adboard || $adboard->points <= 0) {
            return response()->json(['error' => 'Adboard not found or insufficient points'], 400);
        }

        $adboard->decrement('points', 1);

        //session
        $session = new AdSession();
        $session->ip_address = $request->ip();
        $session->user_id = Auth::user()->id;
        $session->device_info = $request->header('Device-Info');
        $session->user_agent = $request->header('User-Agent');
        $session->save();

        //impressions
        $impression = new AdImpression();
        $impression->ad_id = $ad->id;
        $impression->user_id = Auth::user()->id;
        $impression->ad_session_id = $session->id;
        $impression->points_used = 1;
        $impression->save();
        return response()->json(['message' => 'Ad'], 200);
    }
}
