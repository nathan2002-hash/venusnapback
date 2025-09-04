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
            'model' => 'gpt-5-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a prompt expert for Venusnap social platform. Enhance image prompts for memes, quotes, and creative snaps. If user provides text, improve it. If no text (e.g., "create motivational quote"), invent catchy text. Make prompts visually detailed for Ideogram with: 1) Visual details 2) Style 3) Composition 4) Mood 5) Legible text. Return ONLY the enhanced prompt, under 80 words.'
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'max_completion_tokens' => 100, // Reduced from 500 to 100
            //'temperature' => 0.8
        ]);

        if ($response->successful()) {
            $responseData = $response->json();

            // Check if choices array exists and has content
            if (!isset($responseData['choices'][0]['message']['content'])) {
                Log::warning("GPT response missing content", ['response' => $responseData]);
                return $this->enhancePromptFallback($userPrompt);
            }

            $content = trim($responseData['choices'][0]['message']['content']);

            // Check if content is empty
            if (empty($content)) {
                Log::warning("GPT returned empty content", ['response' => $responseData]);
                return $this->enhancePromptFallback($userPrompt);
            }

            return $content;
        }

        // Handle API error
        $errorBody = $response->body();
        Log::error("OpenAI API error: " . $errorBody);
        throw new \Exception('OpenAI API error: ' . $errorBody);

    } catch (\Exception $e) {
        Log::warning("GPT-5 mini enhancement failed: " . $e->getMessage());
        return $userPrompt;
    }
}

private function enhancePromptFallback(string $userPrompt): string
{
    $enhancements = [
        'meme' => 'funny viral meme with bold text, internet humor, popular format, social media trend',
        'quote' => 'inspirational quote with elegant typography, motivational message, beautiful design',
        'bible' => 'biblical inspiration with spiritual imagery, divine message, faith-based, religious art',
        'inspiration' => 'motivational quote with uplifting message, beautiful typography, inspiring design',
        'motivational' => 'inspiring message with powerful words, encouraging design, positive vibes',
        'create' => 'creative design with unique concept, visually striking composition, artistic',
        'make' => 'creative design with unique concept, visually striking composition, artistic'
    ];

    $enhanced = $userPrompt;

    // Add specific enhancements based on keywords
    $foundMatch = false;
    foreach ($enhancements as $keyword => $enhancement) {
        if (stripos($userPrompt, $keyword) !== false) {
            $enhanced .= ', ' . $enhancement;
            $foundMatch = true;
            break;
        }
    }

    // Add general enhancements for all prompts
    $enhanced .= ', high quality, detailed, professional, social media content';

    // Add platform context
    $enhanced .= ', vertical phone wallpaper, ideogram AI generated, legible text, clear typography';

    // Clean up any double commas or extra spaces
    $enhanced = preg_replace('/\s*,\s*,/', ',', $enhanced);
    $enhanced = trim($enhanced, ', ');

    return $enhanced;
}
}
