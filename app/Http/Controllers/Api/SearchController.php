<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Search;
use App\Models\Category;
use App\Models\Album;
use App\Models\Ad;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{

    public function search(Request $request)
{
    $query = $request->query('q');

    $albumSuggestions = Album::where('visibility', 'public')
        ->inRandomOrder()
        ->limit(5)
        ->pluck('name')
        ->toArray();

    $categorySuggestions = Category::inRandomOrder()
        ->limit(5)
        ->pluck('name')
        ->toArray();

    $suggestions = collect($albumSuggestions)
        ->merge($categorySuggestions)
        ->shuffle()
        ->values()
        ->toArray();

    if (empty($query)) {
        return response()->json([
            'results' => [],
            'suggestions' => $suggestions
        ]);
    }

    // Search posts with media relationship
    $posts = Post::with(['album', 'media'])
        ->where('description', 'like', "%$query%")
        ->where('visibility', 'public')
        ->limit(10)
        ->get()
        ->map(function ($post) {
            // Get the first media item from the post
            $thumbnailUrl = null;
            if ($post->media->isNotEmpty()) {
                $firstMedia = $post->media->first();
                $thumbnailUrl = generateSecureMediaUrl($firstMedia->file_path);
            } else if ($post->album) {
                // Fallback to album image if no post media
                $thumbnailUrl = $this->getProfileUrl($post->album);
            }

            return [
                'id' => $post->id,
                'title' => $post->album ? $post->album->name : Str::limit($post->description, 30),
                'description' => Str::limit($post->description, 50),
                'image_url' => $thumbnailUrl,
                'is_album' => false,
                'is_ad' => false,
                'is_trending' => $post->is_trending ?? false,
                'is_verified' => false, // Posts don't have verification
                'album_name' => $post->album ? $post->album->name : null, // Album source for posts
            ];
        });

    // Search albums
    $albums = Album::where('name', 'like', "%$query%")
        ->where('visibility', 'public')
        ->limit(10)
        ->get()
        ->map(function ($album) {
            return [
                'id' => $album->id,
                'title' => $album->name,
                'description' => $album->description ? Str::limit($album->description, 50) : 'Album', // Use album description if available
                'image_url' => $this->getProfileUrl($album),
                'is_album' => true,
                'is_ad' => false,
                'is_trending' => $album->is_trending ?? false,
                'is_verified' => (bool)$album->is_verified,
                'album_name' => null, // Not needed for albums themselves
            ];
        });

    // Search ads
    $ads = Ad::with(['media', 'adboard.album'])
        ->where('status', 'active')
        ->whereHas('adboard', function($q) use ($query) {
            $q->where('status', 'active') // Adboard must be active
            ->where('points', '>', 0)
                ->where(function($subQuery) use ($query) {
                    $subQuery->where('name', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%")
                        ->orWhereHas('album', function($q) use ($query) {
                            $q->where('name', 'like', "%$query%");
                        });
                });
        })
        ->limit(10)
        ->get()
        ->map(function ($ad) {
            // Get ad image (first media item or default)
            $adImage = $ad->media->isNotEmpty()
                ? generateSecureMediaUrl($ad->media->first()->file_path)
                : asset('default/ad.png');

            // Get album image if available
            $albumImage = $ad->adboard && $ad->adboard->album
                ? $this->getProfileUrl($ad->adboard->album)
                : null;

            return [
                'id' => $ad->id,
                'title' => $ad->adboard->name ?? 'Ad',
                'description' => Str::limit($ad->adboard->description ?? '', 50),
                'image_url' => $albumImage ?? $adImage,
                'is_album' => false,
                'is_ad' => true,
                'is_trending' => false,
                'is_verified' => false, // Ads don't have verification
                'album_name' => $ad->adboard && $ad->adboard->album ? $ad->adboard->album->name : null, // Album source for ads
            ];
        });

    // Merge all results
    $mergedResults = collect([])
        ->merge($posts)
        ->merge($albums)
        ->merge($ads)
        ->shuffle();

    if ($mergedResults->isEmpty()) {
        return response()->json([
            'results' => [],
            'suggestions' => $suggestions
        ]);
    }

    // Log search if query is long enough
    if (strlen($query) >= 3) {
        $this->logSearch($request, $query, $mergedResults->count());
    }

    return response()->json($mergedResults);
}

public function discover(Request $request)
{
    // Get trending posts
    $posts = Post::with(['album', 'postmedias'])
        ->where('visibility', 'public')
        ->where('is_trending', true)
        ->inRandomOrder()
        ->limit(8)
        ->get()
        ->map(function ($post) {
            // Get the first media item from the post
            $thumbnailUrl = null;
            if ($post->postmedias->isNotEmpty()) {
                $firstMedia = $post->postmedias->first();
                $thumbnailUrl = generateSecureMediaUrl($firstMedia->file_path);
            } else if ($post->album) {
                // Fallback to album image if no post media
                $thumbnailUrl = $this->getProfileUrl($post->album);
            }

            return [
                'id' => $post->id,
                'title' => $post->album ? $post->album->name : Str::limit($post->description, 30),
                'description' => Str::limit($post->description, 50),
                'image_url' => $thumbnailUrl,
                'is_album' => false,
                'is_ad' => false,
                'is_trending' => $post->is_trending ?? false,
                'is_verified' => false,
                'album_name' => $post->album ? $post->album->name : null,
            ];
        });

    // Get verified albums
    $albums = Album::where('visibility', 'public')
        ->where('is_verified', true)
        ->inRandomOrder()
        ->limit(6)
        ->get()
        ->map(function ($album) {
            return [
                'id' => $album->id,
                'title' => $album->name,
                'description' => $album->description ? Str::limit($album->description, 50) : 'Album',
                'image_url' => $this->getProfileUrl($album),
                'is_album' => true,
                'is_ad' => false,
                'is_trending' => $album->is_trending ?? false,
                'is_verified' => (bool)$album->is_verified,
                'album_name' => null,
            ];
        });

    // Get active ads
    $ads = Ad::with(['media', 'adboard.album'])
        ->where('status', 'active')
        ->whereHas('adboard', function($q) {
            $q->where('status', 'active')
                ->where('points', '>', 0);
        })
        ->inRandomOrder()
        ->limit(4)
        ->get()
        ->map(function ($ad) {
            // Get ad image (first media item or default)
            $adImage = $ad->media->isNotEmpty()
                ? generateSecureMediaUrl($ad->media->first()->file_path)
                : asset('default/ad.png');

            // Get album image if available
            $albumImage = $ad->adboard && $ad->adboard->album
                ? $this->getProfileUrl($ad->adboard->album)
                : null;

            return [
                'id' => $ad->id,
                'title' => $ad->adboard->name ?? 'Ad',
                'description' => Str::limit($ad->adboard->description ?? '', 50),
                'image_url' => $albumImage ?? $adImage,
                'is_album' => false,
                'is_ad' => true,
                'is_trending' => false,
                'is_verified' => false,
                'album_name' => $ad->adboard && $ad->adboard->album ? $ad->adboard->album->name : null,
            ];
        });

    // Merge all discover items and shuffle
    $discoverItems = collect([])
        ->merge($posts)
        ->merge($albums)
        ->merge($ads)
        ->shuffle();

    return response()->json($discoverItems);
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

    private function getHardcodedSuggestions()
    {
        return [
            '#summer2024',
            'travel',
            'food',
            'fitness',
            '#vacation',
            'music',
            'art',
            'photography'
        ];
    }

    private function getRelatedSuggestions($query)
    {
        // You could make this smarter by analyzing the query
        // For now, we'll return a mix of hardcoded and query-based suggestions
        $baseSuggestions = $this->getHardcodedSuggestions();

        // Add some variations based on the query
        if (strlen($query) > 2) {
            array_unshift($baseSuggestions, $query . ' tips');
            array_unshift($baseSuggestions, 'best ' . $query);
        }

        return array_slice($baseSuggestions, 0, 8); // Return max 8 suggestions
    }


    public function logSearch(Request $request, $query, $resultsCount)
    {
        $realIp = $request->header('cf-connecting-ip') ?? $request->ip();
        $ipaddress = $realIp;
        // Only log if the query length is at least 3 characters
        if (strlen($query) < 3) {
            return response()->json(['message' => 'Query must be at least 3 characters'], 400);
        }
        // Log the search with the current query and the count of results
        Search::create([
            'user_id' => Auth::check() ? Auth::id() : null, // Store user ID if logged in
            'query' => $query,
            'results_count' => $resultsCount, // Log the count of search results
            'ip_address' => $ipaddress,
        ]);

        return response()->json(['message' => 'Search logged']);
    }

}
