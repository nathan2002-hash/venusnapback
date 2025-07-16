<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Point;

class HomeController extends Controller
{
    public function terms()
    {
        return view('terms', [
        ]);
    }

    public function privacy()
    {
        return view('policy', [
        ]);
    }

    public function home()
    {
        return view('welcome', [
        ]);
    }

    public function blocked()
    {
        return view('auth.blocked', [
        ]);
    }

    public function childsafety()
    {
        return view('child', [
        ]);
    }

    public function purchase()
    {
        $country = 'USA';
        $packages = Point::where('country', strtoupper($country))
            ->orderBy('points')
            ->get(['points', 'price'])
            ->map(function ($package) {
                return [
                    'points' => (int) $package->points,
                    'price'  => (int) $package->price, // Ensuring price is also an integer
                ];
            });
        return view('chat', [
            'packages'   => $packages,
            'user_points'   => (int) 9000,
            'min_points' => (int) config('points.min_points', 1000),
            'stripekey'   => env('STRIPE_PUBLIC'),
        ]);
    }

    public function deeplink($postId, $mediaId)
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

        return view('deeplink', [
            'post' => $post,
            'media' => $media,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }
}
