<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Album;
use App\Models\LinkShare;
use Illuminate\Support\Facades\Auth;

class DeeplinkController extends Controller
{
    public function post(Request $request, $postId, $mediaId)
    {
        $post = Post::with(['user', 'album'])->findOrFail($postId);
        $media = PostMedia::findOrFail($mediaId);
        $album = $post->album;

        $thumbnailUrl = null;

        if ($album) {
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

        return view('deeplink.post', [
            'post' => $post,
            'media' => $media,
            'thumbnailUrl' => $thumbnailUrl,
            'share' => $share // Pass the share object to the view
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
}
