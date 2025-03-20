<?php

namespace App\Http\Controllers\Api;

use App\Models\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('q'); // Get the search query from the request

        // Fetch suggestions from the posts table
        $suggestions = DB::table('posts')
            ->join('categories', 'posts.type', '=', 'categories.id') // Join categories
            ->join('albums', 'posts.album_id', '=', 'albums.id') // Join albums
            ->where('posts.description', 'like', "%$query%") // Search in descriptions
            ->orWhere('categories.name', 'like', "%$query%") // Search in categories
            ->orWhere('albums.name', 'like', "%$query%") // Search in albums
            ->select(
                'posts.id',
                'posts.description',
                'categories.name as category_name',
                'albums.name as album_name'
            )
            ->limit(10) // Limit the number of suggestions
            ->get();

        // Format the suggestions
        $suggestions = $suggestions->map(function ($post) {
            return [
                'id' => $post->id,
                'description' => $post->description,
                'category' => $post->category_name,
                'album' => $post->album_name,
            ];
        });

        return response()->json($suggestions);
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
