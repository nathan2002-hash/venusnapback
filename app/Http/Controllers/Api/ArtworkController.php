<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Artwork;
use App\Jobs\ArtworkStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $artwork->status = 'pending';
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
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(10);  // Adjust the number per page as necessary

        // Format data before sending it to Flutter
        $formattedArtworks = $artworks->map(function ($artwork) {
            // Generate the S3 URL for the thumbnail image
            $thumbnailUrl = Storage::disk('s3')->url($artwork->thumbnail); // Adjust 's3' if your disk is named differently in config/filesystems.php

            return [
                'id' => $artwork->id,
                'thumbnail' => $thumbnailUrl,  // Use the S3 URL for the thumbnail
                'created_at' => Carbon::parse($artwork->created_at)->format('d M Y H:i'),  // Format the date
            ];
        });

        // Return the paginated and formatted data
        return response()->json([
            'data' => $formattedArtworks,
            'next_page' => $artworks->nextPageUrl(), // URL for the next page
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $artwork = Artwork::find($id);
    
        if (!$artwork) {
            return response()->json(['message' => 'Artwork not found'], 404);
        }
    
        $artwork->delete();
    
        return response()->json(['message' => 'Artwork deleted successfully'], 200);
    }
}
