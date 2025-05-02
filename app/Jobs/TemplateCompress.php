<?php

namespace App\Jobs;

use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;

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
        $path = $this->template->original_template;
        $originalImage = Storage::disk('s3')->get($path);
    
        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);
    
        // Set a max width for resizing while keeping aspect ratio
        $maxWidth = 1200;
    
        // Resize only if the image is larger than maxWidth
        if ($image->width() > $maxWidth) {
            $image = $image->scale(width: $maxWidth);
        }
    
        // Compress to WebP with a lower quality for better performance
        $compressedImage = $image->encode(new WebpEncoder(quality: 75));
    
        // Store compressed image
        $compressedPath = 'uploads/templates/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);
    
        // Update template record
        $this->template->update([
            'status' => 'completed', // Changed from 'compressed' to 'completed'
            'compressed_template' => $compressedPath,
        ]);
    }
}
