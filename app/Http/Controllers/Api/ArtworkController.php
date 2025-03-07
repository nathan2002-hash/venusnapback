<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Artwork;
use App\Jobs\ArtworkStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ArtworkController extends Controller
{
    public function index()
    {
        $artworks = Artwork::all();
        return view('user.artwork.index', [
           'artworks' => $artworks
        ]);
    }

    public function store(Request $request)
    {
        $artwork = new Artwork();
        $artwork->user_id = Auth::user()->id;
        if ($request->hasFile('artwork')) {
            $path = $request->file('artwork')->store('uploads/artworks/originals', 's3');
            $artwork->file_path = $path;
        }
        $artwork->save();

        ArtworkStore::dispatch($artwork->id);

        return response()->json([
            'message' => 'Artwork saved successfully!',
            'artwork' => $artwork
        ]);
    }

    public function fetchArtworks(Request $request)
    {
        $user = Auth::user();  // Get the authenticated user

        // Paginate the artwork (e.g., 10 per page)
        $artworks = Artwork::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);  // Adjust the number per page as necessary

        // Format data before sending it to Flutter
        $formattedArtworks = $artworks->map(function ($artwork) {
            return [
                'id' => $artwork->id,
                'thumbnail' => $artwork->thumbnail, // Assuming 'thumbnail' is the URL
                'created_at' => Carbon::parse($artwork->created_at)->format('d M Y H:i'),  // Format the date
            ];
        });

        // Return the paginated and formatted data
        return response()->json([
            'data' => $formattedArtworks,
            'next_page' => $artworks->nextPageUrl(), // URL for the next page
        ]);
    }
}
