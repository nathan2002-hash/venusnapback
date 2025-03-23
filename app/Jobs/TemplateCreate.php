<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use App\Models\Template;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

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
    public function handle(): void
    {
        $template = Template::find($this->templateId);
        if (!$template || !$template->original_template) {
            return; // Handle edge cases gracefully
        }

        $path = "https://venusnaplondon.s3.eu-west-2.amazonaws.com/uploads/templates/originals/9bTG1hcmS4u6FCH5FhJ6OH01K3FPZ9tPHf4qKXPY.jpg";
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);
        // Direct binary content
        $originalImage = file_get_contents($path, false, $context);
        //$originalImage = file_get_contents($path);

        if (!$originalImage) {
            // Log or handle the error if the image is not found or cannot be fetched
            Log::error("Unable to fetch the image from the URL: {$path}");
            return;
        }

        $manager = new ImageManager(new GdDriver());

        // Read binary directly
        $image = $manager->read($originalImage);

        $compressedImage = $image->encode(new WebpEncoder(quality: 75));

        $compressedPath = 'uploads/templates/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $template->update(['compressed_template' => $compressedPath]);
    }


}
