<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Album;

class ExploreController extends Controller
{
    public function exploreAlbums(Request $request)
    {
        // Validate pagination parameters
        $validated = $request->validate([
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:50'
        ]);

        $page = $validated['page'] ?? 1;
        $limit = $validated['limit'] ?? 8;

        // Get only public albums (excluding private ones)
        $query = Album::where('status', 'active')
            ->where('type', '!=', 'private') // Exclude private albums
            ->withCount([
                'supporters as supporters_count' => function($query) {
                    $query->where('status', 'active');
                },
                'posts as posts_count' => function($query) {
                    $query->whereIn('status', ['active', 'review']);
                }
            ])
            ->orderByDesc('created_at');

        // Paginate the results
        $paginatedAlbums = $query->paginate($limit, ['*'], 'page', $page);

        // Transform the albums for response
        $albums = $paginatedAlbums->getCollection()->map(function ($album) {
            // Determine thumbnail URL based on album type
            $thumbnailUrl = null;
            $coverUrl = null;

            if ($album->type === 'personal' || $album->type === 'creator') {
                $thumbnailUrl = $album->thumbnail_compressed
                    ? generateSecureMediaUrl($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? generateSecureMediaUrl($album->thumbnail_original)
                        : null);

                // Use first post image as cover if available
                $firstPost = $album->posts()
                    ->whereIn('status', ['active', 'review'])
                    ->orderBy('created_at')
                    ->first();

                if ($firstPost && $firstPost->postMedia->isNotEmpty()) {
                    $coverUrl = generateSecureMediaUrl($firstPost->postMedia[0]->filepath);
                }
            } elseif ($album->type === 'business') {
                $thumbnailUrl = $album->business_logo_compressed
                    ? generateSecureMediaUrl($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? generateSecureMediaUrl($album->business_logo_original)
                        : null);

                // For business albums, use business cover if available
                if ($album->cover_photo_compressed) {
                    $coverUrl = generateSecureMediaUrl($album->cover_photo_compressed);
                } elseif ($album->cover_photo_original) {
                    $coverUrl = generateSecureMediaUrl($album->cover_photo_original);
                }
            }

            return [
                'id' => $album->id,
                'name' => $album->name,
                'description' => $album->description,
                'thumbnail_url' => $thumbnailUrl,
                'cover_url' => $coverUrl ?? $thumbnailUrl, // Fallback to thumbnail if no cover
                'is_verified' => (bool) $album->is_verified,
                'supporters_count' => $album->supporters_count,
                'posts_count' => $album->posts_count,
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
