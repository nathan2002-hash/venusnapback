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
                ? Storage::disk('s3')->url($ad->media->first()->file_path)
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
