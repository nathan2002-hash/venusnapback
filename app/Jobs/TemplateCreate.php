<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Template;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class TemplateCreate implements ShouldQueue
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
            // Update status to processing
            $template->update(['status' => 'processing']);

            // Enhanced prompt for background templates
            $finalPrompt = "Create a professional background template suitable for my artwork based on: " .
                          $this->description .
                          ". The background should be clean, visually appealing, and leave space for text placement.";

            // Call DALL-E API

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

                // Download the original image
                $originalImage = Http::get($imageUrl)->body();

                // Generate unique filename
                $originalFileName = 'uploads/templates/original/' . uniqid() . '.png';

                // Store original on S3
                Storage::disk('s3')->put($originalFileName, $originalImage);

                // Process image compression
                //$compressedFileName = 'uploads/templates/compressed/' . pathinfo($originalFileName, PATHINFO_FILENAME) . '.webp';
                TemplateCompress::dispatch($template->id);

                // Deduct points
                $user->decrement('points', 40);

                // Update template record
                $template->update([
                    'original_template' => $originalFileName,
                    'status' => 'awaiting_compression',
                    'type' => 'free',
                    'name' => 'Custom Template - ' . substr($this->description, 0, 20) . '...'
                ]);

                // Update transaction
                $transaction->update([
                    'status' => 'completed',
                    'resource_id' => $template->id,
                    'balance_after' => $user->points,
                    'description' => 'Successfully generated template',
                    'metadata' => json_encode([
                        'template_id' => $template->id,
                        'original_template' => $originalFileName,
                        'api_response' => $response->json()
                    ])
                ]);

                DB::commit();

            } else {
                $error = $response->json('error.message', $response->body());
                throw new \Exception("DALL-E API error: " . $error);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            // Update status to failed
            $template->update(['status' => 'failed']);

            // Update transaction
            $transaction->update([
                'status' => 'failed',
                'description' => 'Template generation failed: ' . $e->getMessage(),
                'metadata' => json_encode([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            ]);

            Log::error("Template generation failed - Template ID: {$this->templateId}, Error: " . $e->getMessage());

            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("TemplateCreate job failed completely: " . $exception->getMessage());
    }

}
