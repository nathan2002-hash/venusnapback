<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ArtworkStore;
use App\Models\Artwork;
use Illuminate\Http\Request;
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

    public function getUserImages(Request $request)
    {
        // Fetch the images for the logged-in user
        $artworks = Artwork::where('user_id', Auth::user()->id)->get(['thumbnail', 'created_at']);

        // Return the image URLs and creation dates in the response
        return response()->json($artworks);
    }
}
