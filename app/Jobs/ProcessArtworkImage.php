<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Artwork;
use App\Models\User;
use App\Models\PointTransaction;

class ProcessArtworkImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
     public function __construct(
        public int $artworkId,
        public string $prompt,
        public int $userId,
        public int $transactionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle()
{
    $artwork = Artwork::findOrFail($this->artworkId);
    $user = User::findOrFail($this->userId);
    $transaction = PointTransaction::findOrFail($this->transactionId);

    DB::beginTransaction();

    try {
        $artwork->update(['status' => 'processing']);

        $finalPrompt = "Create a high-quality digital artwork based on: " . $this->prompt;

        // Ideogram API call
        $response = Http::withHeaders([
            'Api-Key' => env('IDEOGRAM_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->timeout(200)
        ->post('https://api.ideogram.ai/v1/ideogram-v3/generate', [
            'prompt' => $finalPrompt,
            'rendering_speed' => 'TURBO', // Optional: TURBO or STANDARD
            'aspect_ratio' => '9x16', // Optional: if you want specific aspect ratio
            // 'style_type' => 'GENERAL' // Optional: GENERAL, PHOTO, etc.
        ]);

        if ($response->successful()) {
            $responseData = $response->json();

            // Check if data exists and has at least one item
            if (!isset($responseData['data'][0]['url'])) {
                throw new \Exception('Ideogram API response missing image URL');
            }

            $imageUrl = $responseData['data'][0]['url'];
            $imageContents = Http::get($imageUrl)->body();
            $fileName = 'uploads/artworks/originals/' . uniqid() . '.jpeg';
            Storage::disk('s3')->put($fileName, $imageContents);

            // Deduct points within transaction
            $user->decrement('points', 50);

            CompressArtworkImage::dispatch($artwork->id);

            $artwork->update([
                'file_path' => $fileName,
                'status' => 'awaiting_compression',
            ]);

            $transaction->update([
                'status' => 'completed',
                'resource_id' => $artwork->id,
                'balance_after' => $user->points,
                'description' => 'Successfully generated artwork',
                'metadata' => json_encode([
                    'artwork_id' => $artwork->id,
                    'image_path' => $fileName,
                    'api_response' => $responseData,
                    'prompt_used' => $finalPrompt,
                    'generator' => 'ideogram'
                ])
            ]);

            DB::commit();
        } else {
            throw new \Exception('Ideogram API error: '.$response->body());
        }

    } catch (\Exception $e) {
        DB::rollBack();

        $artwork->update(['status' => 'failed']);

        $transaction->update([
            'status' => 'failed',
            'description' => 'Artwork generation failed: '.$e->getMessage(),
            'metadata' => json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ])
        ]);

        Log::error("Artwork generation failed: " . $e->getMessage());
    }
}
}
