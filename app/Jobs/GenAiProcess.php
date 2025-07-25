<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\GenAi;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenAiProcess implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $genaiId,
        public string $description,
        public int $userId,
        public int $transactionId
    ) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $genai = GenAi::findOrFail($this->genaiId);
        $user = User::findOrFail($this->userId);
        $transaction = PointTransaction::findOrFail($this->transactionId);

        DB::beginTransaction();

        try {
            $genai->update(['status' => 'processing']);
            $finalPrompt = "Create a professional advertisement image based on: " . $this->description;

            $response = Http::withToken(env('OPENAI_API_KEY'))
            ->timeout(200)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $finalPrompt,
                'n' => 1,
                'size' => '1024x1792',
                'response_format' => 'url'
            ]);

            if ($response->successful()) {
                $imageUrl = $response->json('data.0.url');
                $imageContents = Http::get($imageUrl)->body();
                $fileName = 'genai/original/' . uniqid() . '.jpeg';
                Storage::disk('s3')->put($fileName, $imageContents);

                // Deduct points within transaction
                $user->decrement('points', 60);

                CompressGenAiImage::dispatch($genai->id);

                $genai->update([
                    'file_path' => $fileName,
                    'status' => 'awaiting_compression',
                ]);

                $transaction->update([
                    'status' => 'completed',
                    'resource_id' => $genai->id,
                    'balance_after' => $user->points,
                    'description' => 'Successfully generated ad',
                    'metadata' => json_encode([
                        'generated_ad_id' => $genai->id,
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

            $genai->update(['status' => 'failed']);

            $transaction->update([
                'status' => 'failed',
                'description' => 'Ad generation failed: '.$e->getMessage(),
                'metadata' => json_encode([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            ]);

            Log::error("Ad generation failed: " . $e->getMessage());
        }
    }
}
