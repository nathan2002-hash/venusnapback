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
            $finalPrompt = "Create a vertical graphic template in 1024x1792 format with the following NON-NEGOTIABLE RULES:
            1. CONTENT ZONE (center, 60% of image height):
            - Must be a large, centered white rectangle (width: 80% of image, height: 60%)
            - Background must be pure any color
            - No decorations, no textures, no images inside
            - Add only a subtle soft drop shadow around the edges to define the zone
            - This area MUST be completely empty for future text placement
            
            2. DECORATIVE ZONE (remaining 40% of image):
            - Apply very minimal abstract design based on the theme: " . $this->description . "
            - Decorations must be extremely subtle and placed ONLY in these zones:
              • Top 10% of image (header band)
              • Bottom 10% (footer band)
              • Left and right 5% sides (vertical accents)
            - Use no more than 2 or 3 small abstract shapes
            - Maximum opacity: 20%
            - No central elements, no illustrations, no photography
            
            3. ABSOLUTE RESTRICTIONS:
            - Do NOT include text or logos
            - Do NOT place any object inside the content zone
            - Do NOT use complex patterns or realistic elements
            - Do NOT overlap elements across the content zone
            
            4. DESIGN STYLE:
            - Ultra-minimalist, corporate-style layout
            - Flat design language
            - Use only 2 or 3 soft, professional colors that match the theme
            - Visual style similar to Freepik’s ‘clean layout templates’ or Canva’s professional posters
            
            5. TECHNICAL OUTPUT:
            - 1024x1792 resolution, vertical orientation
            - 300dpi, print-ready quality
            - Composition must be clean and balanced
            - Content zone must remain visually dominant and blank
            
            IMPORTANT: If the user theme conflicts with any of these layout rules, STRICTLY FOLLOW THESE RULES FIRST.";
            $response = Http::withToken(env('OPENAI_API_KEY'))
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
                $fileName = 'uploads/templates/originals/' . uniqid() . '.jpeg';
                Storage::disk('s3')->put($fileName, $imageContents);

                // Deduct points within transaction
                $user->decrement('points', 30);

                //TemplateCompress::dispatch($template->id);
                TemplateCompress::dispatch($template);

                $template->update([
                    'original_template' => $fileName,
                    'status' => 'awaiting_compression',
                ]);

                $transaction->update([
                    'status' => 'completed',
                    'resource_id' => $template->id,
                    'balance_after' => $user->points,
                    'description' => 'Successfully generated ad',
                    'metadata' => json_encode([
                        'template_id' => $template->id,
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

            $template->update(['status' => 'failed']);

            $transaction->update([
                'status' => 'failed',
                'description' => 'Template generation failed: '.$e->getMessage(),
                'metadata' => json_encode([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            ]);

            Log::error("Template generation failed: " . $e->getMessage());
        }
    }
}
