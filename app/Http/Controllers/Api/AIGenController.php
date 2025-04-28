<?php

namespace App\Http\Controllers\Api;

use App\Models\GenAi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AIGenController extends Controller
{
    private $stableDiffusionUrl = 'https://api.stability.ai/v1/generation/stable-diffusion-v1-6/text-to-image';

        public function generateAd(Request $request)
        {
            $user = auth()->user();

            // Validate request
            $request->validate([
                'description' => 'required|min:20',
            ]);

            // Check user has enough points
            if ($user->points < 30) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points'
                ], 400);
            }

            try {
                // Call Stable Diffusion API
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('STABLE_DIFFUSION_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post($this->stableDiffusionUrl, [
                    'text_prompts' => [
                        [
                            'text' => $request->description,
                            'weight' => 1
                        ]
                    ],
                    'cfg_scale' => 7,
                    'height' => 800,
                    'width' => 512,
                    'samples' => 1,
                    'steps' => 30,
                ]);

                if ($response->successful()) {
                    // Save the generated image
                    $imageData = $response->body();
                    $fileName = 'genai/' . uniqid() . '.png';
                    Storage::put($fileName, $imageData);

                    // Deduct points
                    $user->decrement('points', 30);

                    $genai = new GenAi();
                    $genai->user_id = Auth::user()->id;
                    $genai->provider = 'stable fusion';
                    $genai->venusnap_points = 30;
                    $genai->file_path = $fileName;
                    $genai->original_description = $request->description;
                    $genai->type = 'Ad';
                    $genai->save();
                    // Save ad to database
                    return response()->json([
                        'success' => true,
                        'genai_id' => $genai->id,
                        'file_path' => Storage::url($fileName)
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate image'
                ], 500);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        public function regenerateAd(Request $request, $id)
        {
            // Similar implementation to generateAd but for existing ads
        }

        public function getAd($id)
        {
            $ad = auth()->user()->ads()->findOrFail($id);

            return response()->json([
                'id' => $ad->id,
                'original_description' => $ad->original_description,
                'edited_description' => $ad->edited_description,
                'image_url' => Storage::url($ad->image_path),
                'created_at' => $ad->created_at->toDateTimeString()
            ]);
        }
}
