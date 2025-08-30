<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;
use Illuminate\Support\Facades\Auth;

class ExploreController extends Controller
{
 public function exploreAlbums(Request $request)
{
    $validated = $request->validate([
        'page' => 'sometimes|integer|min:1',
        'limit' => 'sometimes|integer|min:1|max:50'
    ]);

    $page = $validated['page'] ?? 1;
    $limit = $validated['limit'] ?? 8;
    $userId = Auth::user()->id;

    $query = Album::where('status', 'active')
        ->where('visibility', '!=', 'private')
        ->withCount([
            'supporters as supporters_count' => function($query) {
                $query->where('status', 'active');
            },
            'posts as posts_count' => function($query) {
                $query->whereIn('status', ['active', 'review']);
            }
        ])
        // check if the current user supports this album
        ->when($userId, function ($q) use ($userId) {
            $q->withCount([
                'supporters as is_supporter' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('status', 'active');
                }
            ]);
        })
        ->inRandomOrder();

    $paginatedAlbums = $query->paginate($limit, ['*'], 'page', $page);

    $albums = $paginatedAlbums->getCollection()->map(function ($album) {
        $thumbnailUrl = null;
        $coverUrl = null;

        if ($album->type === 'personal' || $album->type === 'creator') {
            $thumbnailUrl = $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : null);

            $coverUrl = $thumbnailUrl;
        } elseif ($album->type === 'business') {
            $thumbnailUrl = $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
                    : null);

            if ($album->cover_image_compressed) {
                $coverUrl = generateSecureMediaUrl($album->cover_image_compressed);
            } elseif ($album->cover_image_original) {
                $coverUrl = generateSecureMediaUrl($album->cover_image_original);
            } else {
                $coverUrl = $thumbnailUrl;
            }
        }

        return [
            'id' => $album->id,
            'name' => $album->name,
            'description' => $album->description,
            'thumbnail_url' => $thumbnailUrl,
            'cover_url' => $coverUrl ?? $thumbnailUrl,
            'is_verified' => (bool) $album->is_verified,
            'supporters_count' => $album->supporters_count,
            'posts_count' => $album->posts_count,
            'is_supporter' => isset($album->is_supporter) && $album->is_supporter > 0, // 👈 true/false
            'created_at' => $album->created_at->toIso8601String(),
        ];
    });

    return response()->json([
        'albums' => $albums,
        'has_more' => $paginatedAlbums->hasMorePages(),
        'current_page' => $paginatedAlbums->currentPage(),
        'total' => $paginatedAlbums->total(),
    ]);
}
}
