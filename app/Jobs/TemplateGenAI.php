<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Template;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TemplateGenAI implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $templateId;
    protected $description;
    protected $userId;
    protected $transactionId;

    public function __construct($templateId, $description, $userId, $transactionId)
    {
        $this->templateId = $templateId;
        $this->description = $description;
        $this->userId = $userId;
        $this->transactionId = $transactionId;
    }

    /**
     * Execute the job.
     */
    public function handle()
{
    $template = Template::findOrFail($this->templateId);
    $user = User::findOrFail($this->userId);
    $transaction = PointTransaction::findOrFail($this->transactionId);

    DB::beginTransaction();

    try {
        $template->update(['status' => 'processing']);

        // Enhance the template prompt using GPT-4.1 mini
        $enhancedPrompt = $this->enhanceTemplatePromptWithGPT4Mini($this->description);

        // Save the enhanced prompt for reference
        //$template->update(['refine_prompt' => $enhancedPrompt]);

        // Ideogram API call for template image
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
            $fileName = 'uploads/templates/originals/' . uniqid() . '.jpeg';
            Storage::disk('s3')->put($fileName, $imageContents);

            // Deduct points
            $user->decrement('points', 50);

            TemplateCompress::dispatch($template);

            $template->update([
                'original_template' => $fileName,
                'status' => 'awaiting_compression',
            ]);

            $transaction->update([
                'status' => 'completed',
                'resource_id' => $template->id,
                'balance_after' => $user->points,
                'description' => 'Successfully generated template',
                'metadata' => json_encode([
                    'template_id' => $template->id,
                    'image_path' => $fileName,
                    'api_response' => $responseData,
                    'enhanced_prompt' => $enhancedPrompt
                ])
            ]);

            DB::commit();
        } else {
            throw new \Exception('Ideogram API error: ' . $response->body());
        }

    } catch (\Exception $e) {
        DB::rollBack();

        $template->update(['status' => 'failed']);

        $transaction->update([
            'status' => 'failed',
            'description' => 'Template generation failed: ' . $e->getMessage(),
            'metadata' => json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ])
        ]);

        Log::error("Template generation failed: " . $e->getMessage());
    }
}

// Enhance template prompt for Ideogram
private function enhanceTemplatePromptWithGPT4Mini(string $description): string
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
                    'content' => 'You are a creative AI prompt expert specialized in abstract and background images for social media templates.
Do NOT include any words, letters, or text in the image.
Enhance the user input into a visually striking, high-quality template prompt for Ideogram.
Focus ONLY on colors, style, composition, shapes, mood, and atmosphere.
The image should be fully abstract or decorative, with no literal text or labels.'
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
        Log::warning("GPT-4.1 mini template enhancement failed: " . $e->getMessage());
        return $description;
    }
}

}
