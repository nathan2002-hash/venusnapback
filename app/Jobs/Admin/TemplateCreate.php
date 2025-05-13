<?php

namespace App\Jobs\Admin;

use Exception;
use App\Models\Template;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class TemplateCreate implements ShouldQueue
{
    use Queueable;
    public $templateId;

    /**
     * Create a new job instance.
     */
    public function __construct($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Find the template
            $template = Template::find($this->templateId);
            if (!$template) {
                Log::error("Template not found for ID: {$this->templateId}");
                return;
            }

            // Check if the original template path exists
            $path = $template->original_template;
            if (!$path || !Storage::disk('s3')->exists($path)) {
                Log::error("Original template path is missing or file does not exist in S3: {$path}");
                return;
            }

            // Read the original image from S3
            $originalImage = Storage::disk('s3')->get($path);
            if (!$originalImage) {
                Log::error("Failed to read original image from S3: {$path}");
                return;
            }

            // Compress the image
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($originalImage);
            $compressedImage = $image->encode(new WebpEncoder(quality: 75));

            // Save the compressed image to S3
            $compressedPath = 'uploads/templates/compressed/' . basename($path);
            Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

            // Update the template with the compressed path
            $template->update(['compressed_template' => $compressedPath]);

            $template->update([
                'compressed_template' => $compressedPath,
                'status' => 'completed',
            ]);

            Log::info("Successfully compressed and saved template ID: {$this->templateId} to {$compressedPath}");
        } catch (Exception $e) {
            Log::error("Error processing template ID: {$this->templateId}. Error: " . $e->getMessage());
            throw $e; // Re-throw the exception to let Laravel handle the retry logic
        }
    }
}
