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
use OpenAI\Client;

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

            // Enhance the prompt using GPT-3.5 Turbo
            $enhancedPrompt = $this->enhancePromptWithGPT5Nano($this->prompt);

            // Ideogram API call with enhanced prompt
            $response = Http::withHeaders([
                'Api-Key' => env('IDEOGRAM_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(200)
            ->post('https://api.ideogram.ai/v1/ideogram-v3/generate', [
                'prompt' => $enhancedPrompt,
                'aspect_ratio' => '9x16',
                'rendering_speed' => 'TURBO',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (!isset($responseData['data'][0]['url'])) {
                    throw new \Exception('Ideogram API response missing image URL');
                }

                $imageUrl = $responseData['data'][0]['url'];
                $imageContents = Http::get($imageUrl)->body();
                $fileName = 'uploads/artworks/originals/' . uniqid() . '.jpeg';
                Storage::disk('s3')->put($fileName, $imageContents);

                // Deduct points
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
                        'original_prompt' => $this->prompt,
                        'enhanced_prompt' => $enhancedPrompt,
                        'prompt_used' => $enhancedPrompt,
                        'generator' => 'ideogram',
                        'aspect_ratio' => '9x16'
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

    private function enhancePromptWithGPT5Nano(string $userPrompt): string
{
    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->timeout(30)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-5-nano',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a prompt engineering expert helping generate engaging images for Venusnap, a social platform where users post creative snaps, memes, motivational quotes, and album covers.
If the user provides text, keep and improve it.
If the user does not provide text (e.g., "create a motivational quote" or "make a meme"), invent a short, catchy and original text that fits the request.
Always make the prompt visually detailed and creative for Ideogram, focusing on:
1) Visual details
2) Style references
3) Composition
4) Mood/atmosphere
5) Strong, legible text placement.

Return ONLY the enhanced prompt, no explanations. Keep it under 100 words.'
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'max_completion_tokens' => 100,
            'temperature' => 0.7
        ]);

        if ($response->successful()) {
            return trim($response->json()['choices'][0]['message']['content']);
        }

        throw new \Exception('OpenAI API error: ' . $response->body());

    } catch (\Exception $e) {
        Log::warning("GPT-5 nano enhancement failed: " . $e->getMessage());
        return $userPrompt; // Return original prompt as fallback
    }
}
}
