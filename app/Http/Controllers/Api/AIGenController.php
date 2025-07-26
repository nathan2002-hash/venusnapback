<?php

namespace App\Http\Controllers\Api;

use App\Models\GenAi;
use App\Models\PointTransaction;
use App\Jobs\GenAiProcess;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AIGenController extends Controller
{
   public function generateAd(Request $request)
    {
        $user = Auth::user();
        $request->validate(['description' => 'required|min:20']);

        // Create transaction record immediately
        $transaction = PointTransaction::create([
            'user_id' => $user->id,
            'points' => 60,
            'type' => 'ad_generation',
            'resource_id' => '1',
            'status' => 'pending',
            'description' => 'Attempt to generate new ad',
            'balance_before' => $user->points,
            'balance_after' => $user->points // Will be updated if successful
        ]);

        if ($user->points < 60) {
            $transaction->update([
                'status' => 'failed',
                'description' => 'Insufficient points for ad generation',
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
            // Create pending record
            $genai = GenAi::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'provider' => 'OPEN_AI',
                'original_description' => $request->description,
                'venusnap_points' => 60,
                'type' => 'Ad',
                'point_transaction_id' => $transaction->id // Link to transaction
            ]);

            // Dispatch job with transaction ID
            GenAiProcess::dispatch($genai->id, $request->description, $user->id, $transaction->id);

            return response()->json([
                'success' => true,
                'genai_id' => (string) $genai->id,
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
                'message' => 'Failed to initiate ad generation',
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ], 500);
        }
    }

    // public function regenerateAd(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     $originalAd = GenAi::where('user_id', $user->id)->findOrFail($id);

    //     // Check user has enough points
    //     if ($user->points < 30) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Insufficient points'
    //         ], 400);
    //     }

    //     try {
    //         $description = $request->edited_description ?? $originalAd->original_description;

    //         // Call Stable Diffusion API (SD3)
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . env('STABLE_DIFFUSION_API_KEY'),
    //             'Accept' => 'image/*',
    //         ])
    //         ->asMultipart()
    //         ->post($this->stableDiffusionUrl, [
    //             [
    //                 'name' => 'prompt',
    //                 'contents' => $description
    //             ],
    //             [
    //                 'name' => 'output_format',
    //                 'contents' => 'jpeg'
    //             ],
    //             [
    //                 'name' => 'none',
    //                 'contents' => '',
    //                 'filename' => 'none'
    //             ]
    //         ]);

    //         if ($response->successful()) {
    //             // Save the new image
    //             $imageData = $response->body();
    //             $fileName = 'genai/' . uniqid() . '.jpeg';
    //             Storage::put($fileName, $imageData);

    //             // Deduct points
    //             $user->decrement('points', 30);

    //             // Create new GenAi record
    //             $genai = new GenAi();
    //             $genai->user_id = $user->id;
    //             $genai->provider = 'stable diffusion';
    //             $genai->venusnap_points = 30;
    //             $genai->file_path = $fileName;
    //             $genai->original_description = $description;
    //             $genai->type = 'Ad';
    //             $genai->save();

    //             return response()->json([
    //                 'success' => true,
    //                 'genai_id' => $genai->id,
    //                 'file_path' => generateSecureMediaUrl($fileName)
    //             ]);
    //         }

    //         $errorResponse = $response->json();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $errorResponse['message'] ?? 'Failed to regenerate image'
    //         ], 500);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function getAd($id)
    {
        $genai = GenAi::findOrFail($id);

        // Return status and data only when generation is complete
        if ($genai->status !== 'completed') {
            return response()->json([
                'status' => $genai->status,
                'message' => 'Generation in progress'
            ]);
        }

        // Only return file URL if status is complete and file exists
        $imageUrl = $genai->file_path_compress ? generateSecureMediaUrl($genai->file_path_compress) : null;

        return response()->json([
            'status' => 'completed',
            'id' => $genai->id,
            'original_description' => $genai->original_description,
            'edited_description' => $genai->edited_description,
            'image_url' => $imageUrl,
            'created_at' => $genai->created_at->toDateTimeString()
        ]);
    }

    public function recentAds()
    {
        return GenAi::where('user_id', Auth::user()->id)
            ->where('type', 'Ad')
            ->where('status', 'completed') // Only completed ads
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get()
            ->map(function ($genai) {
                // Safely handle file path
                $imageUrl = $genai->file_path_compress ? generateSecureMediaUrl($genai->file_path_compress) : null;

                return [
                    'id' => $genai->id,
                    'image_url' => $imageUrl,
                    'original_description' => $genai->original_description,
                    'created_at' => $genai->created_at->toDateTimeString(),
                    'status' => $genai->status
                ];
            })
            ->filter(function ($ad) {
                // Ensure we only return ads with valid image URLs
                return !empty($ad['image_url']);
            })
            ->values(); // Reset array keys after filtering
    }

     public function placeholders()
{
    return [
        [
            'id' => 'placeholder-1',
            'description' => 'Modern tech product with clean background',
            'prompt' => 'A modern smartphone on a white minimalist background, clean shadows, centered for ad display'
        ],
        [
            'id' => 'placeholder-2',
            'description' => 'Elegant fashion ad',
            'prompt' => 'A full-body portrait of a stylish woman in elegant fashion wear, standing against a pastel backdrop for a fashion advertisement'
        ],
        [
            'id' => 'placeholder-3',
            'description' => 'Food promotion poster',
            'prompt' => 'A vibrant and colorful ad poster of a juicy hamburger with fries and a drink, placed on a wooden table with blurred background, high-quality'
        ],
    ];
}


    public function checkStatus($id)
    {
        $genai = GenAi::where('user_id', Auth::user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => $genai->status,
            'image_url' => $genai->file_path_compress ? generateSecureMediaUrl($genai->file_path_compress) : null,
            // ... other fields
        ]);
    }

    public function GenPoints(Request $request)
    {
        $available = Auth::user()->points;

        $available_points = (int) $available;
        // Return response in JSON format
        return response()->json([
            'available_points' => $available_points,
            'gen_points' => (int) 60
        ]);
    }

    public function GenImages()
    {
        return GenAi::where('user_id', Auth::user()->id)
            ->where('type', 'Ad')
            ->where('status', 'completed') // Only completed ads
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($genai) {
                // Safely handle file path
                $imageUrl = $genai->file_path_compress ? generateSecureMediaUrl($genai->file_path_compress) : null;
                $imageUrlDownload = $genai->file_path ? generateSecureMediaUrl($genai->file_path) : null;

                return [
                    'id' => $genai->id,
                    'image_url' => $imageUrl,
                    'image_url_download' => $imageUrlDownload,
                    'original_description' => $genai->original_description,
                    'created_at' => $genai->created_at->toDateTimeString(),
                    'status' => $genai->status
                ];
            })
            ->filter(function ($ad) {
                // Ensure we only return ads with valid image URLs
                return !empty($ad['image_url']);
            })
            ->values(); // Reset array keys after filtering
    }

}
