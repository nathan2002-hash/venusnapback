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

            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->timeout(200)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'dall-e-3',
                    'prompt' => $finalPrompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'response_format' => 'url'
                ]);

            if ($response->successful()) {
                $imageUrl = $response->json('data.0.url');
                $imageContents = Http::get($imageUrl)->body();
                $fileName = 'artworks/original/' . uniqid() . '.jpeg';
                Storage::disk('s3')->put($fileName, $imageContents);

                // Deduct points within transaction
                $user->decrement('points', 100);

                // Dispatch compression job
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
                        'api_response' => $response->json()
                    ])
                ]);

                DB::commit();
            } else {
                throw new \Exception('DALL-E API error: '.$response->body());
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
