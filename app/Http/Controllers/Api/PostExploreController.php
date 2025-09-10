<?php

namespace App\Http\Controllers\Api;

use App\Models\Ad;
use App\Models\Post;
use App\Models\Adboard;
use App\Jobs\AdClickJob;
use App\Models\AdSession;
use App\Models\Supporter;
use App\Models\AdCtaClick;
use Illuminate\Support\Str;
use App\Models\AdImpression;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessBatchEarningsJob;
use Illuminate\Support\Facades\Queue;
 use App\Models\VenusnapSystem;

class PostExploreController extends Controller
{
    public function index(Request $request)
{
    $userId = Auth::id();
    $limit = $request->query('limit', 8); // Default to 8 items per request
    $targetPostId = $request->query('target_post');

    $targetPost = null;
    $isTargetPostIncluded = false;

    // Fetch target post if specified and valid
    if ($targetPostId) {
        $targetPost = Post::with(['postmedias', 'album.supporters'])
            ->where('id', $targetPostId)
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->first();

        // if ($targetPost) {
        //     // Track the explore visit if ref is provided
        //     $ref = $request->query('ref');
        //     if ($ref) {
        //         // Record the explore visit with referral code
        //         ExploreVisit::create([
        //             'post_id' => $targetPostId,
        //             'short_code' => $ref,
        //             'user_id' => $userId,
        //             'ip_address' => $request->ip(),
        //             'user_agent' => $request->userAgent(),
        //         ]);
        //     }
        // }
    }

    // 1. Get recommendations (40% of batch)
    $recommendationCount = min(4, ceil($limit * 0.4));
    $recommendations = Recommendation::with(['post.postmedias', 'post.album.supporters'])
        ->where('user_id', $userId)
        ->where('status', 'active')
        ->inRandomOrder()
        ->take($recommendationCount)
        ->get();

    // Mark as fetched
    if ($recommendations->isNotEmpty()) {
        Recommendation::whereIn('id', $recommendations->pluck('id'))
            ->update(['status' => 'seen']);
    }

    // 2. Get regular posts (50% of batch)
    $postCount = min(4, $limit - $recommendationCount);

    // If we have a target post, reduce regular post count by 1
    if ($targetPost) {
        $postCount = max(0, $postCount - 1);
    }

    $posts = Post::with(['postmedias', 'album.supporters'])
        ->where('status', 'active')
        ->where('visibility', 'public')
        ->when($targetPost, function ($query) use ($targetPostId) {
            // Exclude target post from regular posts if it exists
            return $query->where('id', '!=', $targetPostId);
        })
        ->inRandomOrder()
        ->take($postCount)
        ->get();

    // 3. Show ads with 80% chance
    $ads = collect(); // default empty
    $shouldShowAds = rand(1, 100) <= 80; // 80% chance

    if ($shouldShowAds) {
        $adCount = max(1, min(2, ceil($limit * 0.2)));
        $ads = Ad::with(['media', 'adboard.album'])
            ->where('status', 'active')
            ->whereHas('adboard', function($query) {
                $query->where('points', '>', 0);
            })
            ->inRandomOrder()
            ->take($adCount)
            ->get();
    }

    // Transform all items
    $items = collect();

    // Add target post first if it exists (ensures position 1-4)
    if ($targetPost) {
        $items->push($this->transformTargetPost($targetPost));
        $isTargetPostIncluded = true;
    }

    // Add other items
    $items = $items
        ->merge($this->transformRecommendations($recommendations))
        ->merge($this->transformPosts($posts))
        ->merge($this->transformAds($ads));

    // Shuffle but keep target post in first 4 positions if it exists
    if ($targetPost && $items->count() > 1) {
        $items = $this->shuffleWithTargetInFirstFour($items, $targetPostId);
    } else {
        $items = $items->shuffle();
    }

    // Batch push for processing
    $batchId = Str::uuid()->toString();
    $fetchedPostIds = collect()
        ->merge($recommendations->pluck('post.id'))
        ->merge($posts->pluck('id'))
        ->values()
        ->toArray();

    // Add target post ID if it was included
    if ($isTargetPostIncluded) {
        $fetchedPostIds[] = $targetPostId;
    }

    Queue::push(new ProcessBatchEarningsJob([
        'batch_id' => $batchId,
        'fetched_posts' => $fetchedPostIds,
        'ads_included' => $ads->isNotEmpty(),
    ]));

    return response()->json([
        'items' => $items,
        'has_more' => true, // Infinite content assumption
        'limit' => $limit,
        'target_post_included' => $isTargetPostIncluded,
    ]);
}

private function transformTargetPost($post)
{
    $album = $post->album;

    return [
        'type' => 'post',
        'id' => $post->id,
        'album_name' => $album->name ?? 'Unknown',
        'profile' => $this->getProfileUrl($album),
        'post_media' => $post->postmedias->map(function ($media) {
            return [
                'id' => $media->id,
                'filepath' => generateSecureMediaUrl($media->file_path_compress),
                'sequence_order' => $media->sequence_order,
            ];
        })->toArray(),
        'is_verified' => (bool) $album->is_verified,
        'supporters_count' => (string) ($album->supporters->count() ?? 0),
        'is_ad' => false,
        'is_target_post' => true, // Add this flag for frontend
        'created_at' => $post->created_at->toDateTimeString(),
    ];
}

private function shuffleWithTargetInFirstFour($items, $targetPostId)
{
    // Separate target post from other items
    $targetItem = $items->firstWhere('id', $targetPostId);
    $otherItems = $items->where('id', '!=', $targetPostId);

    // Shuffle other items
    $shuffledOthers = $otherItems->shuffle();

    // Determine random position for target post (0-3)
    $targetPosition = rand(0, min(3, $shuffledOthers->count()));

    // Insert target post at random position in first 4
    $result = collect();

    // Add items before target position
    if ($targetPosition > 0) {
        $result = $result->merge($shuffledOthers->take($targetPosition));
    }

    // Add target post
    $result->push($targetItem);

    // Add remaining items
    if ($shuffledOthers->count() > $targetPosition) {
        $result = $result->merge($shuffledOthers->slice($targetPosition));
    }

    return $result;
}

// Update your existing transform methods to include is_target_post flag
private function transformRecommendations($recommendations)
{
    return $recommendations->map(function ($rec) {
        $post = $rec->post;
        $album = $post->album;

        return [
            'type' => 'recommendation',
            'id' => $post->id,
            'album_name' => $album->name ?? 'Unknown',
            'profile' => $this->getProfileUrl($album),
            'post_media' => $post->postmedias->map(function ($media) {
                return [
                    'id' => $media->id,
                    'filepath' => generateSecureMediaUrl($media->file_path_compress),
                    'sequence_order' => $media->sequence_order,
                ];
            })->toArray(),
            'is_verified' => (bool) $album->is_verified,
            'supporters_count' => (string) ($album->supporters->count() ?? 0),
            'is_ad' => false,
            'is_target_post' => false, // Add this flag
            'created_at' => $post->created_at->toDateTimeString(),
        ];
    });
}

private function transformPosts($posts)
{
    return $posts->map(function ($post) {
        $album = $post->album;

        return [
            'type' => 'post',
            'id' => $post->id,
            'album_name' => $album->name ?? 'Unknown',
            'profile' => $this->getProfileUrl($album),
            'post_media' => $post->postmedias->map(function ($media) {
                return [
                    'id' => $media->id,
                    'filepath' => generateSecureMediaUrl($media->file_path_compress),
                    'sequence_order' => $media->sequence_order,
                ];
            })->toArray(),
            'is_verified' => (bool) $album->is_verified,
            'supporters_count' => (string) ($album->supporters->count() ?? 0),
            'is_ad' => false,
            'is_target_post' => false, // Add this flag
            'created_at' => $post->created_at->toDateTimeString(),
        ];
    });
}

private function transformAds($ads)
{
    return $ads->map(function ($ad) {
        $album = $ad->adboard->album ?? null;

        return [
            'type' => 'ad',
            'id' => $ad->id,
            'album_name' => $album->name ?? 'Advertiser',
            'profile' => $this->getProfileUrl($album),
            'post_media' => $ad->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'filepath' => generateSecureMediaUrl($media->file_path),
                    'sequence_order' => $media->sequence_order,
                ];
            })->toArray(),
            'is_verified' => false,
            'supporters_count' => '0',
            'is_ad' => true,
            'is_target_post' => false, // Add this flag
            'cta_name' => $ad->cta_name,
            'cta_link' => $ad->cta_link,
            'created_at' => $ad->created_at->toDateTimeString(),
        ];
    });
}

    private function getProfileUrl($album)
    {
        if (!$album) {
            return asset('default/profile.png');
        }

        if (in_array($album->type, ['personal', 'creator'])) {
            return $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : asset('default/profile.png'));
        }

        if ($album->type === 'business') {
            return $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
                    : asset('default/profile.png'));
        }

        return asset('default/profile.png');
    }

    public function getAdById(Request $request, $id)
    {
        // Fetch the ad from the database
        $ad = Ad::find($id);

        $user = Auth::user();
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        // Check if ad exists
        if (!$ad) {
            return response()->json(['error' => 'Ad not found'], 404);
        }

        // Prepare media URLs
        $mediaUrls = $ad->media->map(function ($media) {
            return generateSecureMediaUrl($media->file_path);
        });

        $album = $ad->adboard->album ?? null;

        $existingSupport = Supporter::where('user_id', $user->id)
            ->where('album_id', $album->id)
            ->first();

            if ($existingSupport) {
                $supportstatus = true;
            } else {
                $supportstatus = false;
            }

        // Determine profile image
        $defaultProfile = asset('default/profile.png');
        $profileImage = $defaultProfile;

        if ($album) {
            if ($album->type == 'personal' || $album->type == 'creator') {
                $profileImage = $album->thumbnail_compressed
                    ? generateSecureMediaUrl($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? generateSecureMediaUrl($album->thumbnail_original)
                        : $defaultProfile);
            } elseif ($album->type == 'business') {
                $profileImage = $album->business_logo_compressed
                    ? generateSecureMediaUrl($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? generateSecureMediaUrl($album->business_logo_original)
                        : $defaultProfile);
            }
        }

        AdClickJob::dispatch(
            $ad->id,
            $ipaddress,
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
                'album_id' => $album->id,
            ],
            'supporters' => $album ? $album->supporters->count() : 0,
            'support' => $supportstatus,
            'backgroundimage' => generateSecureMediaUrl('uploads/ads/ads3.jpg'),
            'album_id' => $album->id,
            'cta_name' => $ad->cta_name,
            'cta_link' => $ad->cta_link,
            'cta_type' => $ad->cta_type,
        ], 200);
    }

    public function sendAdSeenRequest(Request $request)
    {
        $ad = Ad::find($request->ad_id);
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $adboard = Adboard::find($ad->adboard_id);
        if (!$adboard || $adboard->points <= 0) {
            return response()->json(['error' => 'Adboard not found or insufficient points'], 400);
        }

        $pointsUsed = 6; // Seen ad costs 6 points
        $adboard->decrement('points', $pointsUsed);

        // Get Venusnap system and calculate money value
        $venusnap = VenusnapSystem::first();
        $moneyValue = $pointsUsed / $venusnap->points_per_dollar;

        // Update Venusnap system
        $venusnap->increment('system_money', $moneyValue);
        $venusnap->increment('total_points_spent', $pointsUsed);

        // Session
        $session = new AdSession();
        $session->ip_address = $ipaddress;
        $session->user_id = Auth::user()->id;
        $session->device_info = $request->header('Device-Info');
        $session->user_agent = $request->header('User-Agent');
        $session->save();

        // Impressions
        $impression = new AdImpression();
        $impression->ad_id = $ad->id;
        $impression->user_id = Auth::user()->id;
        $impression->ad_session_id = $session->id;
        $impression->points_used = $pointsUsed;
        $impression->save();

        return response()->json(['message' => 'Ad', 'points_used' => $pointsUsed, 'money_added' => $moneyValue], 200);
    }

    public function sendAdCtaClick(Request $request, $id)
    {
        $ad = Ad::find($id);
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $adboard = Adboard::find($ad->adboard_id);
        if (!$adboard || $adboard->points <= 0) {
            return response()->json(['error' => 'Adboard not found or insufficient points'], 400);
        }

        $pointsUsed = 2; // CTA click costs 2 points
        $adboard->decrement('points', $pointsUsed);

        // Get Venusnap system and calculate money value
        $venusnap = VenusnapSystem::first();
        $moneyValue = $pointsUsed / $venusnap->points_per_dollar;

        // Update Venusnap system
        $venusnap->increment('system_money', $moneyValue);
        $venusnap->increment('total_points_spent', $pointsUsed);

        // Session
        $session = new AdSession();
        $session->ip_address = $ipaddress;
        $session->user_id = Auth::user()->id;
        $session->device_info = $request->header('Device-Info');
        $session->user_agent = $request->header('User-Agent');
        $session->save();

        // CTA Clicks
        $adcta = new AdCtaClick();
        $adcta->ad_id = $ad->id;
        $adcta->user_id = Auth::user()->id;
        $adcta->ad_session_id = $session->id;
        $adcta->points_used = $pointsUsed;
        $adcta->save();

        return response()->json(['message' => 'Ad Cta', 'points_used' => $pointsUsed, 'money_added' => $moneyValue], 200);
    }
}
