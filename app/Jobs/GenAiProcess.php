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

        // Enhance the prompt using GPT-4.1 mini
        $enhancedPrompt = $this->enhanceAdPromptWithGPT4Mini($this->description);

        // Save the enhanced prompt in refine_prompt column
        $genai->update(['edited_description' => $enhancedPrompt]);

        // Ideogram API call
        $response = Http::withHeaders([
            'Api-Key' => env('IDEOGRAM_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->timeout(200)
        ->post('https://api.ideogram.ai/v1/ideogram-v3/generate', [
            'prompt' => $enhancedPrompt,
            'aspect_ratio' => '9x16',
            'rendering_speed' => 'DEFAULT',
        ]);

        if ($response->successful()) {
            $responseData = $response->json();

            if (!isset($responseData['data'][0]['url'])) {
                throw new \Exception('Ideogram API response missing image URL');
            }

            $imageUrl = $responseData['data'][0]['url'];
            $imageContents = Http::get($imageUrl)->body();
            $fileName = 'genai/original/' . uniqid() . '.jpeg';
            Storage::disk('s3')->put($fileName, $imageContents);

            // Deduct points
            $user->decrement('points', 80);

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
                    'api_response' => $responseData,
                    'enhanced_prompt' => $enhancedPrompt
                ])
            ]);

            DB::commit();
        } else {
            throw new \Exception('Ideogram API error: '.$response->body());
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

// Enhance ad prompt for Ideogram
private function enhanceAdPromptWithGPT4Mini(string $description): string
{
    try {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->timeout(30)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a creative AI prompt expert specialized in advertisements for social media and marketing. Turn user input into a visually stunning, professional ad prompt for Ideogram. Focus on: 1) Visual details 2) Style 3) Composition 4) Mood 5) Branding appeal. Return ONLY the enhanced prompt.'
                ],
                [
                    'role' => 'user',
                    'content' => $description
                ]
            ],
            'max_completion_tokens' => 300
        ]);

        if ($response->successful()) {
            $content = $response->json()['choices'][0]['message']['content'] ?? '';
            return empty(trim($content)) ? $description : trim($content);
        }

        throw new \Exception('OpenAI API error: ' . $response->body());

    } catch (\Exception $e) {
        Log::warning("GPT-4.1 mini ad enhancement failed: " . $e->getMessage());
        return $description;
    }
}

}
