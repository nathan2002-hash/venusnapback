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
    private $stableDiffusionUrl = 'https://api.stability.ai/v2beta/stable-image/generate/sd3';

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
            // Call Stable Diffusion API (SD3)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('STABLE_DIFFUSION_API_KEY'),
                'Accept' => 'image/*',
            ])
            ->asMultipart()
            ->post($this->stableDiffusionUrl, [
                [
                    'name' => 'prompt',
                    'contents' => $request->description
                ],
                [
                    'name' => 'output_format',
                    'contents' => 'jpeg'
                ],
                // You can add more parameters here
                [
                    'name' => 'none',
                    'contents' => '',
                    'filename' => 'none'
                ]
            ]);

            if ($response->successful()) {
                // Save the generated image
                $imageData = $response->body();
                $fileName = 'genai/' . uniqid() . '.jpeg';
                Storage::put($fileName, $imageData);

                // Deduct points
                $user->decrement('points', 30);

                // Save to GenAi model
                $genai = new GenAi();
                $genai->user_id = $user->id;
                $genai->provider = 'stable diffusion';
                $genai->venusnap_points = 30;
                $genai->file_path = $fileName;
                $genai->original_description = $request->description;
                $genai->type = 'Ad';
                $genai->save();

                return response()->json([
                    'success' => true,
                    'genai_id' => $genai->id,
                    'file_path' => Storage::url($fileName)
                ]);
            }

            // Handle API error response
            $errorResponse = $response->json();
            return response()->json([
                'success' => false,
                'message' => $errorResponse['message'] ?? 'Failed to generate image'
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
        $user = auth()->user();
        $originalAd = GenAi::where('user_id', $user->id)->findOrFail($id);

        // Check user has enough points
        if ($user->points < 30) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points'
            ], 400);
        }

        try {
            $description = $request->edited_description ?? $originalAd->original_description;

            // Call Stable Diffusion API (SD3)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('STABLE_DIFFUSION_API_KEY'),
                'Accept' => 'image/*',
            ])
            ->asMultipart()
            ->post($this->stableDiffusionUrl, [
                [
                    'name' => 'prompt',
                    'contents' => $description
                ],
                [
                    'name' => 'output_format',
                    'contents' => 'jpeg'
                ],
                [
                    'name' => 'none',
                    'contents' => '',
                    'filename' => 'none'
                ]
            ]);

            if ($response->successful()) {
                // Save the new image
                $imageData = $response->body();
                $fileName = 'genai/' . uniqid() . '.jpeg';
                Storage::put($fileName, $imageData);

                // Deduct points
                $user->decrement('points', 30);

                // Create new GenAi record
                $genai = new GenAi();
                $genai->user_id = $user->id;
                $genai->provider = 'stable diffusion';
                $genai->venusnap_points = 30;
                $genai->file_path = $fileName;
                $genai->original_description = $description;
                $genai->type = 'Ad';
                $genai->save();

                return response()->json([
                    'success' => true,
                    'genai_id' => $genai->id,
                    'file_path' => Storage::url($fileName)
                ]);
            }

            $errorResponse = $response->json();
            return response()->json([
                'success' => false,
                'message' => $errorResponse['message'] ?? 'Failed to regenerate image'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
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
