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
            $finalPrompt = "Create an image based on" . $this->description;
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
