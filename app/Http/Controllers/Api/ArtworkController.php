<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Artwork;
use App\Jobs\ArtworkStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessArtworkImage;
use Illuminate\Support\Facades\Auth;
use App\Models\PointTransaction;

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
            ->whereIn('status', ['active', 'completed'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);  // Adjust the number per page as necessary

        // Format data before sending it to Flutter
        $formattedArtworks = $artworks->map(function ($artwork) {
            // Generate the S3 URL for the thumbnail image
            $thumbnailUrl = generateSecureMediaUrl($artwork->thumbnail);
            $downloadUrl = generateSecureMediaUrl($artwork->file_path); // Adjust 's3' if your disk is named differently in config/filesystems.php

            return [
                'id' => $artwork->id,
                'thumbnail' => $thumbnailUrl,
                'downloadimage' => $downloadUrl,
                'downloadfilename' => 'VEN_' . $artwork->id,
                'created_at' => Carbon::parse($artwork->created_at)->format('d M Y H:i'),  // Format the date
            ];
        });

        // Return the paginated and formatted data
        return response()->json([
            'data' => $formattedArtworks,
            'next_page' => $artworks->nextPageUrl(), // URL for the next page
        ]);
    }

    public function destroy($id)
    {
        $artwork = Artwork::find($id);

        if (!$artwork) {
            return response()->json(['message' => 'Artwork not found'], 404);
        }

        if ($artwork->user_id !== Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized to delete this artwork'], 403);
        }

        $artwork->delete();

        return response()->json(['message' => 'Artwork deleted successfully'], 200);
    }

    public function GenPoints(Request $request)
    {
        $available = Auth::user()->points;

        $available_points = (int) $available;
        // Return response in JSON format
        return response()->json([
            'available_points' => $available_points,
            'gen_points' => (int) 30
        ]);
    }

    public function generateImage(Request $request)
    {
        $user = Auth::user();
        $request->validate(['prompt' => 'required|min:20']);

        // Create transaction record immediately
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'points' => 30,
            'type' => 'image_generation',
            'resource_id' => '1',
            'status' => 'pending',
            'description' => 'Attempt to generate new image',
            'balance_before' => $user->points,
            'balance_after' => $user->points // Will be updated if successful
        ]);

        if ($user->points < 30) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'Insufficient points for image generation',
                'metadata' => json_encode([
                    'required_points' => 30,
                    'available_points' => $user->points
                ])
            ]);

            return response()->json([
                'message' => 'Insufficient points',
                'transaction_id' => $transaction->id
            ], 400);
        }

        try {
            // Create pending record in artworks table
            $artwork = Artwork::create([
                'user_id' => $user->id,
                'content' => $request->prompt,
                'status' => 'pending',
                'background_color' => '#FFFFFF', // Default white background
                'content_color' => '#000000', // Default black text
            ]);

            // Dispatch job with transaction ID
            ProcessArtworkImage::dispatch($artwork->id, $request->prompt, $user->id, $transaction->id);

            return response()->json([
                'success' => true,
                'artwork_id' => (string) $artwork->id,
                'transaction_id' => $transaction->id,
                'status' => 'pending'
            ]);

        } catch (\Exception $e) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'System error: '.$e->getMessage(),
                'metadata' => json_encode([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            ]);

            return response()->json([
                'message' => 'Failed to initiate image generation',
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ], 500);
        }
    }

    public function getImage($id)
    {
        $artwork = Artwork::where('user_id', Auth::user()->id)
                         ->findOrFail($id);

        // Return status and data only when generation is complete
        if ($artwork->status !== 'completed') {
            return response()->json([
                'status' => $artwork->status,
                'message' => 'Generation in progress'
            ]);
        }

        // Only return file URL if status is complete and file exists
        $imageUrl = $artwork->thumbnail ? generateSecureMediaUrl($artwork->thumbnail) : null;

        return response()->json([
            'status' => 'completed',
            'id' => $artwork->id,
            'original_prompt' => $artwork->content,
            'image_url' => $imageUrl,
            'created_at' => $artwork->created_at->toDateTimeString()
        ]);
    }

    public function recentImages()
    {
        return Artwork::where('user_id', Auth::user()->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get()
            ->map(function ($artwork) {
                $imageUrl = $artwork->thumbnail ? generateSecureMediaUrl($artwork->thumbnail) : null;

                return [
                    'id' => $artwork->id,
                    'image_url' => $imageUrl,
                    'original_prompt' => $artwork->content,
                    'created_at' => $artwork->created_at->toDateTimeString(),
                    'status' => $artwork->status
                ];
            })
            ->filter(function ($artwork) {
                return !empty($artwork['image_url']);
            })
            ->values();
    }

    public function promptExamples()
    {
        return [
            [
                'id' => 'example-1',
                'description' => 'Fantasy landscape',
                'prompt' => 'A magical fantasy landscape with floating islands, waterfalls, and a castle in the clouds, digital art style'
            ],
            [
                'id' => 'example-2',
                'description' => 'Cyberpunk city',
                'prompt' => 'A neon-lit cyberpunk city at night with flying cars and holographic advertisements, rain-soaked streets'
            ],
            [
                'id' => 'example-3',
                'description' => 'Peaceful nature scene',
                'prompt' => 'A serene mountain lake at sunrise with mist rising from the water and pine trees in the foreground'
            ],
        ];
    }

    public function checkStatus($id)
    {
        $artwork = Artwork::where('user_id', Auth::user()->id)
                         ->findOrFail($id);

        return response()->json([
            'status' => $artwork->status,
            'image_url' => $artwork->thumbnail ? generateSecureMediaUrl($artwork->thumbnail) : null,
        ]);
    }

    public function regenerateImage(Request $request, $id)
    {
        $user = Auth::user();
        $artwork = Artwork::where('user_id', $user->id)
                         ->findOrFail($id);

        $request->validate(['prompt' => 'required|min:20']);

        // Create transaction record
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'points' => 50,
            'type' => 'image_regeneration',
            'resource_id' => $artwork->id,
            'status' => 'pending',
            'description' => 'Attempt to regenerate image',
            'balance_before' => $user->points,
            'balance_after' => $user->points
        ]);

        if ($user->points < 50) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'Insufficient points for image regeneration'
            ]);

            return response()->json([
                'message' => 'Insufficient points',
                'transaction_id' => $transaction->id
            ], 400);
        }

        try {
            // Update artwork with new prompt and reset status
            $artwork->update([
                'content' => $request->prompt,
                'status' => 'pending',
                'file_path' => null,
                'thumbnail' => null
            ]);

            ProcessArtworkImage::dispatch($artwork->id, $request->prompt, $user->id, $transaction->id);

            return response()->json([
                'success' => true,
                'artwork_id' => (string) $artwork->id,
                'transaction_id' => $transaction->id,
                'status' => 'pending'
            ]);

        } catch (\Exception $e) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'System error: '.$e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to regenerate image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDownloadUrl($id)
    {
        $artwork = Artwork::where('user_id', Auth::user()->id)
                        ->findOrFail($id);

        if (!$artwork->file_path) {
            return response()->json(['error' => 'Image not available'], 404);
        }

        // Use your helper to generate a secure URL
        $url = generateSecureMediaUrl($artwork->file_path);

        return response()->json([
            'download_url' => $url,
            'filename' => basename($artwork->file_path)
        ]);
    }
}
