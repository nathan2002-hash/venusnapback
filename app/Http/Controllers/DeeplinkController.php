<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Album;
use App\Models\LinkShare;
use App\Models\LinkAdShare;
use App\Models\Ad;
use App\Models\LinkVisit;
use Illuminate\Support\Facades\Auth;

class DeeplinkController extends Controller
{
    public function postmedia(Request $request, $postId, $mediaId)
    {
        $post = Post::with(['user', 'album'])->findOrFail($postId);
        $media = PostMedia::findOrFail($mediaId);
        $album = $post->album;

        $thumbnailUrl = null;

        if ($album) {
            if ($album->type === 'personal' || $album->type === 'creator') {
                $thumbnailUrl = $album->thumbnail_compressed
                    ? generateSecureMediaUrl($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? generateSecureMediaUrl($album->thumbnail_original)
                        : null);
            } elseif ($album->type === 'business') {
                $thumbnailUrl = $album->business_logo_compressed
                    ? generateSecureMediaUrl($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? generateSecureMediaUrl($album->business_logo_original)
                        : null);
            }
        }

        $share = LinkShare::with('user')->where('short_code', $request->ref)->first();

        // Track visit if share exists
        if ($share) {
            $visitData = [
                'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_info' => $request->header('Device-Info'),
                'referrer' => $request->ref,
                'link_share_id' => $share->id,
                'is_logged_in' => Auth::check(),
            ];

            if (Auth::check()) {
                $visitData['user_id'] = Auth::id();
            }

            $share->visits()->create($visitData);
        }

        return view('deeplink.postmedia', [
            'post' => $post,
            'media' => $media,
            'thumbnailUrl' => $thumbnailUrl,
            'share' => $share // Pass the share object to the view
        ]);
    }

    public function post(Request $request, $post)
    {
        $post = Post::with(['user', 'album'])->findOrFail($post);
        $album = $post->album;

        $thumbnailUrl = null;

        if ($album) {
            if ($album->type === 'personal' || $album->type === 'creator') {
                $thumbnailUrl = $album->thumbnail_compressed
                    ? generateSecureMediaUrl($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? generateSecureMediaUrl($album->thumbnail_original)
                        : null);
            } elseif ($album->type === 'business') {
                $thumbnailUrl = $album->business_logo_compressed
                    ? generateSecureMediaUrl($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? generateSecureMediaUrl($album->business_logo_original)
                        : null);
            }
        }

        // Track visit if share exists
        return view('deeplink.post', [
            'post' => $post,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    public function album(Request $request, $albumId)
    {
        $album = Album::with(['user', 'posts'])
            ->findOrFail($albumId);

        $thumbnailUrl = null;

        if ($album->type === 'personal' || $album->type === 'creator') {
            $thumbnailUrl = $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : null);
        } elseif ($album->type === 'business') {
            $thumbnailUrl = $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
                    : null);
        }

        $share = LinkShare::with('user')->where('short_code', $request->ref)->first();

        // Track visit if share exists
        if ($share) {
            $visitData = [
                'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_info' => $request->header('Device-Info'),
                'referrer' => $request->ref,
                'link_share_id' => $share->id,
                'is_logged_in' => Auth::check(),
            ];

            if (Auth::check()) {
                $visitData['user_id'] = Auth::id();
            }

            $share->visits()->create($visitData);
        }

        return view('deeplink.album', [
            'album' => $album,
            'thumbnailUrl' => $thumbnailUrl,
            'share' => $share
        ]);
    }

    public function ad(Request $request, $shortCode)
    {
        // Find the share record
        $share = LinkAdShare::with(['ad.adboard', 'user'])->where('short_code', $shortCode)->firstOrFail();
        $ad = $share->ad;
        $adBoard = $ad->adboard;

        // Get the album (assuming ad is associated with an album)
        $album = $ad->adboard->album; // Or whatever your relationship is called

        // Determine thumbnail URL
        $thumbnailUrl = $this->getAlbumThumbnailUrl($album);

        // Track the visit
        if ($share) {
            $visitData = [
                'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_info' => $request->header('Device-Info'),
                'referrer' => $share->short_code,
                'link_share_id' => $share->id,
                'is_logged_in' => Auth::check(),
            ];

            if (Auth::check()) {
                $visitData['user_id'] = Auth::id();
            }
        }

        return view('deeplink.ad', [
            'ad' => $ad,
            'adBoard' => $adBoard,
            'album' => $album,
            'thumbnailUrl' => $thumbnailUrl,
            'share' => $share
        ]);
    }

    protected function getAlbumThumbnailUrl($album)
    {
        if (!$album) return null;

        if ($album->type === 'personal' || $album->type === 'creator') {
            return $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : null);
        } elseif ($album->type === 'business') {
            return $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
                    : null);
        }

        return null;
    }

    protected function trackVisit(Request $request, LinkAdShare $share)
    {
        $visitData = [
            'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'device_info' => $request->header('Device-Info'),
            'referrer' => $request->ref,
            'is_logged_in' => Auth::check(),
        ];

        if (Auth::check()) {
            $visitData['user_id'] = Auth::id();
        }

        $share->visits()->create($visitData);
    }

    // app/Http/Controllers/ExploreController.php
    public function explore(Request $request, $postId = null)
    {
        $post = null;
        $thumbnailUrl = null;
        $description = "Discover amazing content on Venusnap Explore!";

        // If a specific post ID is provided, fetch it
        if ($postId) {
            $post = Post::with(['album'])->find($postId);

            if ($post && $post->album) {
                $album = $post->album;

                // Get thumbnail URL
                if ($album->type === 'personal' || $album->type === 'creator') {
                    $thumbnailUrl = $album->thumbnail_compressed
                        ? generateSecureMediaUrl($album->thumbnail_compressed)
                        : ($album->thumbnail_original
                            ? generateSecureMediaUrl($album->thumbnail_original)
                            : null);
                } elseif ($album->type === 'business') {
                    $thumbnailUrl = $album->business_logo_compressed
                        ? generateSecureMediaUrl($album->business_logo_compressed)
                        : ($album->business_logo_original
                            ? generateSecureMediaUrl($album->business_logo_original)
                            : null);
                }

                // Update description for specific post
                $description = "Check out this post from '".($post->album->name ?? 'an album')."' on Venusnap Explore!";
            }
        }

        // Track visit if ref exists
       $ref = $request->query('ref');

        $share = LinkShare::with('user')->where('short_code', $request->ref)->first();

        // Track visit if share exists
        if ($share) {
            $visitData = [
                'ip_address' => $request->header('cf-connecting-ip') ?? $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'device_info' => $request->header('Device-Info'),
                'referrer' => $request->ref,
                'link_share_id' => $share->id,
                'is_logged_in' => Auth::check(),
            ];

            if (Auth::check()) {
                $visitData['user_id'] = Auth::id();
            }

            $share->visits()->create($visitData);
        }

        return view('deeplink.explore', [
            'post' => $post,
            'thumbnailUrl' => $thumbnailUrl,
            'description' => $description,
            'postId' => $postId,
            'ref' => $ref,
        ]);
    }
}
