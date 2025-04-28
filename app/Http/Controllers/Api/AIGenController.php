<?php

namespace App\Http\Controllers\Api;

use App\Models\GenAi;
use App\Jobs\GenAiProcess;
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

        $request->validate(['description' => 'required|min:20']);

        if ($user->points < 30) {
            return response()->json(['message' => 'Insufficient points'], 400);
        }

        try {
            // Create pending record first
            $genai = GenAi::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'provider' => 'OPEN_AI',
                'original_description' => $request->description,
                'venusnap_points' => 30,
                'type' => 'Ad'
            ]);

            // Dispatch job to handle async generation
            GenAiProcess::dispatch($genai->id, $request->description, $user->id);

            return response()->json([
                'success' => true,
                'genai_id' => $genai->id,
                'status' => 'pending'
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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
            $genai = GenAi::findOrFail($id);

            return response()->json([
                'id' => $genai->id,
                'original_description' => $genai->original_description,
                'edited_description' => $genai->edited_description,
                'image_url' => Storage::url($genai->file_path),
                'created_at' => $genai->created_at->toDateTimeString()
            ]);
        }
}
