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
use Illuminate\Support\Facades\Storage;

class PostExploreController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->query('limit', 8); // Default to 8 items per request

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
        $posts = Post::with(['postmedias', 'album.supporters'])
            ->where('status', 'active')
            ->where('visibility', 'public')
            ->inRandomOrder()
            ->take($postCount)
            ->get();

        // 3. Get ads (10-20% of batch)
        // $adCount = max(1, min(2, ceil($limit * 0.2)));
        // $ads = Ad::with(['media', 'adboard.album'])
        //     ->where('status', 'active')
        //     ->whereHas('adboard', function($query) {
        //         $query->where('points', '>', 0);
        //     })
        //     ->inRandomOrder()
        //     ->take($adCount)
        //     ->get();
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
        $items = collect()
            ->merge($this->transformRecommendations($recommendations))
            ->merge($this->transformPosts($posts))
            ->merge($this->transformAds($ads))
            ->shuffle();

             // ✨ Batch push for processing (no processing here)
            $batchId = Str::uuid()->toString();
            $fetchedPostIds = collect()
                ->merge($recommendations->pluck('post.id'))
                ->merge($posts->pluck('id'))
                ->values()
                ->toArray();

            Queue::push(new ProcessBatchEarningsJob([
                'batch_id' => $batchId,
                'fetched_posts' => $fetchedPostIds,
                'ads_included' => $ads->isNotEmpty(),
            ]));

        return response()->json([
            'items' => $items,
            'has_more' => true, // Infinite content assumption
            'limit' => $limit,
        ]);
    }

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
                        'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                        'sequence_order' => $media->sequence_order,
                    ];
                })->toArray(),
                'is_verified' => (bool) $album->is_verified,
                //'is_verified' => $album->is_verified ?? false,
                'supporters_count' => (string) ($album->supporters->count() ?? 0),
                'is_ad' => false,
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
                        'filepath' => Storage::disk('s3')->url($media->file_path_compress),
                        'sequence_order' => $media->sequence_order,
                    ];
                })->toArray(),
                'is_verified' => (bool) $album->is_verified,
                'supporters_count' => (string) ($album->supporters->count() ?? 0),
                'is_ad' => false,
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
                        'filepath' => Storage::disk('s3')->url($media->file_path),
                        'sequence_order' => $media->sequence_order,
                    ];
                })->toArray(),
                'is_verified' => false,
                'supporters_count' => '0',
                'is_ad' => true,
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
                ? Storage::disk('s3')->url($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? Storage::disk('s3')->url($album->thumbnail_original)
                    : asset('default/profile.png'));
        }

        if ($album->type === 'business') {
            return $album->business_logo_compressed
                ? Storage::disk('s3')->url($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? Storage::disk('s3')->url($album->business_logo_original)
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
            return Storage::disk('s3')->url($media->file_path);
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
            'backgroundimage' => env('adbg'),
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

        $adboard->decrement('points', 1);

        //session
        $session = new AdSession();
        $session->ip_address = $ipaddress;
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

    public function sendAdCtaClick(Request $request, $id)
    {
        $ad = Ad::find($id);
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;

        $adboard = Adboard::find($ad->adboard_id);
        if (!$adboard || $adboard->points <= 0) {
            return response()->json(['error' => 'Adboard not found or insufficient points'], 400);
        }

        $adboard->decrement('points', 2);

        //session
        $session = new AdSession();
        $session->ip_address = $ipaddress;
        $session->user_id = Auth::user()->id;
        $session->device_info = $request->header('Device-Info');
        $session->user_agent = $request->header('User-Agent');
        $session->save();

        //impressions
        $adcta = new AdCtaClick();
        $adcta->ad_id = $ad->id;
        $adcta->user_id = Auth::user()->id;
        $adcta->ad_session_id = $session->id;
        $adcta->points_used = 2;
        $adcta->save();
        return response()->json(['message' => 'Ad Cta'], 200);
    }
}
