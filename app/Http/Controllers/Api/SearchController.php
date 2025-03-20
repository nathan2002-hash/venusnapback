<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Search;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q'); // Get the search query from the request

        // Fetch trending categories (define your own trending logic)
        $trendingCategories = Category::limit(3) // Apply limit to the query
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

            $suggestions = Post::with(['album']) // No need to load category here
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
            'description' => $post->description,
            'category' => $categoryName, // Dynamically fetched category name
            'album' => $post->album->name ?? null,
            'image_url' => $thumbnailUrl, // Use the dynamically determined thumbnail URL
            'is_trending' => false, // Not trending
        ];
    });

    // Merge results and trending categories
    $mergedResults = $trendingCategories->merge($suggestions);

    return response()->json($mergedResults);
    }

    public function logSearch(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'No query provided'], 400);
        }

        // Save the search using Eloquent
        Search::create([
            'user_id' => Auth::check() ? Auth::id() : null, // Store user ID if logged in
            'query' => $query,
            'results_count' => DB::table('posts')
                ->where('description', 'like', "%$query%")
                ->orWhere('type', 'like', "%$query%")
                ->orWhere('album_id', 'like', "%$query%")
                ->count(),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Search logged']);
    }

}
