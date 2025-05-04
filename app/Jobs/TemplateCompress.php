<?php

namespace App\Jobs;

use App\Models\Template;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class TemplateCompress implements ShouldQueue
{
    use Queueable;
    public $template;

    /**
     * Create a new job instance.
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            if (empty($this->template->original_template)) {
                throw new \Exception('Original template path is empty');
            }

            $path = $this->template->original_template;

            if (!Storage::disk('s3')->exists($path)) {
                throw new \Exception("Original template file not found at: $path");
            }

            $originalImage = Storage::disk('s3')->get($path);

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($originalImage);

            // Set a max width for resizing while keeping aspect ratio
            $maxWidth = 1200;

            // Resize only if the image is larger than maxWidth
            if ($image->width() > $maxWidth) {
                $image = $image->scale(width: $maxWidth);
            }

            // Compress to WebP
            $compressedImage = $image->encode(new WebpEncoder(quality: 75));

            // Store compressed image
            $compressedPath = 'uploads/templates/compressed/' . basename($path);
            Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

            // Update template record
            $this->template->update([
                'status' => 'completed',
                'compressed_template' => $compressedPath,
            ]);

            Log::info("Template compressed successfully", [
                'template_id' => $this->template->id,
                'compressed_path' => $compressedPath
            ]);

        } catch (\Exception $e) {
            Log::error("Template compression failed: " . $e->getMessage(), [
                'template_id' => $this->template->id,
                'error' => $e->getTraceAsString()
            ]);

            // Optionally update template status
            $this->template->update(['status' => 'compression_failed']);

            throw $e; // Re-throw to mark job as failed
        }
    }
}
