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
    public function seajrch(Request $request)
    {
        $query = $request->query('q'); // Get the search query from the request

        // Fetch trending categories (limit the query before calling get)
        $trendingCategories = Category::limit(3)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'description' => $category->name,
                    'category' => $category->name,
                    'album' => null, // No album for categories
                    'image_url' => null, // No image for categories
                    'is_trending' => true, // Mark as trending
                ];
            });

        // Search posts based on the query
        $suggestions = Post::with(['album'])
            ->where('description', 'like', "%$query%")
            ->orWhereHas('album', function ($q) use ($query) {
                $q->where('name', 'like', "%$query%");
            })
            ->limit(10)
            ->get()
            ->map(function ($post) {
                // Determine the album's thumbnail URL based on album type
                $thumbnailUrl = null;
                if ($post->album) {
                    if ($post->album->type == 'personal' || $post->album->type == 'creator') {
                        $thumbnailUrl = $post->album->thumbnail_compressed
                            ? Storage::disk('s3')->url($post->album->thumbnail_compressed)
                            : ($post->album->thumbnail_original
                                ? Storage::disk('s3')->url($post->album->thumbnail_original)
                                : null);
                    } elseif ($post->album->type == 'business') {
                        $thumbnailUrl = $post->album->business_logo_compressed
                            ? Storage::disk('s3')->url($post->album->business_logo_compressed)
                            : ($post->album->business_logo_original
                                ? Storage::disk('s3')->url($post->album->business_logo_original)
                                : null);
                    } else {
                        $thumbnailUrl = 'https://example.com/default-thumbnail.jpg'; // Default image URL
                    }
                }

                // Determine the category based on the album type (or post type)
                $categoryName = null;
                if ($post->type) { // Assuming 'type' is the category_id
                    $categoryName = Category::find($post->type)->name ?? null;
                }

                return [
                    'id' => $post->id,
                    'description' => Str::limit($post->description, 50),
                    'category' => $categoryName, // Dynamically fetched category name
                    'album' => $post->album->name ?? null,
                    'image_url' => $thumbnailUrl, // Use the dynamically determined thumbnail URL
                    'is_trending' => false, // Not trending
                ];
            });

        // Merge results and trending categories
        $mergedResults = $trendingCategories->merge($suggestions);

        // Shuffle the merged results to randomize the order each time
        $mergedResults = $mergedResults->shuffle();
        $resultsCount = $suggestions->count();

         // Only log search when the query has 3 or more characters
         if (strlen($query) >= 3) {
            $this->logSearch($request, $query, $resultsCount); // Call the logSearch method to log the search
        }

        return response()->json($mergedResults);
    }

    public function search(Request $request)
{
    $query = $request->query('q');

    // Search posts
    $posts = Post::with(['album'])
        ->where('description', 'like', "%$query%")
        ->limit(10)
        ->get()
        ->map(function ($post) {
            $thumbnailUrl = null;
            if ($post->album) {
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
            ];
        });

    // Search albums
    $albums = Album::where('name', 'like', "%$query%")
        ->limit(10)
        ->get()
        ->map(function ($album) {
            return [
                'id' => $album->id,
                'title' => $album->name,
                'description' => 'Album',
                'image_url' => $this->getProfileUrl($album),
                'is_album' => true,
                'is_ad' => false,
                'is_trending' => $album->is_trending ?? false,
            ];
        });

   // Search ads
$ads = Ad::with(['media', 'adboard.album'])
    ->where('status', 'published')
    ->whereHas('adboard', function($q) use ($query) {
        $q->where('points', '>', 0)
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
            ? Storage::disk('s3')->url($ad->media->first()->path)
            : asset('default/ad.png');
        
        // Get album image if available
        $albumImage = $ad->adboard && $ad->adboard->album
            ? $this->getProfileUrl($ad->adboard->album)
            : null;

        return [
            'id' => $ad->id,
            'title' => $ad->adboard->name ?? 'Ad', // Get title from adboard
            'description' => Str::limit($ad->adboard->description ?? '', 50), // Get description from adboard
            'image_url' => $albumImage ?? $adImage,
            'is_album' => false,
            'is_ad' => true,
            'is_trending' => false,
        ];
    });

    // Merge all results
    $mergedResults = collect([])
        ->merge($posts)
        ->merge($albums)
        ->merge($ads)
        ->shuffle();

    // Log search if query is long enough
    if (strlen($query) >= 3) {
        $this->logSearch($request, $query, $mergedResults->count());
    }

    return response()->json($mergedResults);
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

    public function logSearch(Request $request, $query, $resultsCount)
    {
        // Only log if the query length is at least 3 characters
        if (strlen($query) < 3) {
            return response()->json(['message' => 'Query must be at least 3 characters'], 400);
        }
        // Log the search with the current query and the count of results
        Search::create([
            'user_id' => Auth::check() ? Auth::id() : null, // Store user ID if logged in
            'query' => $query,
            'results_count' => $resultsCount, // Log the count of search results
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Search logged']);
    }

}
