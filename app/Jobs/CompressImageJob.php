<?php

namespace App\Jobs;

use App\Models\PostMedia;
use Intervention\Image\Image;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\JpegEncoder;

class CompressImageJob implements ShouldQueue
{
    use Queueable;
    public $postMedia;

    /**
     * Create a new job instance.
     */
    public function __construct(PostMedia $postMedia)
    {
        $this->postMedia = $postMedia;
    }

    /**
     * Execute the job.
     */
    // public function handle()
    // {
    //     $path = $this->postMedia->file_path;
    //     $originalImage = Storage::disk('s3')->get($path);

    //     $manager = new ImageManager(new GdDriver());
    //     $image = $manager->read($originalImage);

    //     // Set a max width for resizing while keeping aspect ratio
    //     $maxWidth = 1200; // Adjust based on Venusnap's needs

    //     // Resize only if the image is larger than maxWidth
    //     if ($image->width() > $maxWidth) {
    //         $image = $image->scale(width: $maxWidth);
    //     }

    //     // Compress to WebP with a lower quality for better performance
    //     $compressedImage = $image->encode(new WebpEncoder(quality: 80)); // Reduce quality for smaller file size

    //     // Store compressed image
    //     $compressedPath = 'uploads/posts/compressed/' . basename($path);
    //     Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

    //     // Update media record
    //     $this->postMedia->update([
    //         'status' => 'compressed',
    //         'file_path_compress' => $compressedPath,
    //     ]);

    //     // Check if all media for the post are compressed
    //     $post = $this->postMedia->post; // Assuming PostMedia belongsTo Post

    //     $allCompressed = $post->postmedias()->where('status', '!=', 'compressed')->doesntExist();

    //     if ($allCompressed) {
    //         $post->update(['status' => 'active']);
    //     }
    // }

    // public function handle()
    // {
    //     $path = $this->postMedia->file_path;
    //     $originalImage = Storage::disk('s3')->get($path);

    //     $manager = new ImageManager(new GdDriver());
    //     $image = $manager->read($originalImage);

    //     // Smart resizing thresholds
    //     $maxDimension = 2000; // Absolute maximum
    //     $targetDimension = 1200; // Ideal target size

    //     // Progressive scaling for different size ranges
    //     if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
    //         $image->scaleDown($maxDimension, $maxDimension);
    //     } elseif ($image->width() > $targetDimension || $image->height() > $targetDimension) {
    //         $image->scaleDown($targetDimension, $targetDimension);
    //     }

    //     // Apply mild sharpening if resized to maintain text clarity
    //     if ($image->width() !== $manager->read($originalImage)->width()) {
    //         $image->sharpen(10);
    //     }

    //     // Always use WebP with optimized settings
    //     $compressedImage = $image->encode(new WebpEncoder(
    //         $this->calculateOptimalQuality($image),
    //         6 // Best compression method
    //     ));

    //     $compressedPath = 'uploads/posts/compressed/' . pathinfo($path, PATHINFO_FILENAME) . '_opt.webp';
    //     Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

    //     $this->postMedia->update([
    //         'status' => 'compressed',
    //         'file_path_compress' => $compressedPath,
    //     ]);

    //     // Post-processing check
    //     $this->checkPostCompletion();
    // }

    // protected function calculateOptimalQuality(Image $image): int
    // {
    //     // Base quality matrix
    //     return match(true) {
    //         ($image->width() > 1800 || $image->height() > 1800) => 90, // Large images get higher quality
    //         ($image->width() < 800 && $image->height() < 800) => 80,   // Small images can tolerate more compression
    //         default => 85                                             // Default balanced quality
    //     };
    // }

    public function handle()
    {
        $path = $this->postMedia->file_path;
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);

        // Detect if image contains text (simple heuristic)
        $hasText = $this->detectText($image);

        // Smart resizing with text consideration
        $maxDimension = $hasText ? 1600 : 2000; // Lower max for text images
        $targetDimension = $hasText ? 1000 : 1200;

        if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
            $image->scaleDown($maxDimension, $maxDimension);
        } elseif ($image->width() > $targetDimension || $image->height() > $targetDimension) {
            $image->scaleDown($targetDimension, $targetDimension);
        }

        // Format selection logic
        if ($hasText) {
            // Use PNG for text-heavy images
            $compressedImage = $image->encode(new PngEncoder());
            $extension = 'png';
        } else {
            // Use optimized JPEG for other images
            $compressedImage = $image->encode(new JpegEncoder(
                quality: $this->calculateOptimalQuality($image),
                progressive: true // Better perceived loading
            ));
            $extension = 'jpg';
        }

        $compressedPath = 'uploads/posts/compressed/' . pathinfo($path, PATHINFO_FILENAME) . '_opt.' . $extension;
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);

        $this->checkPostCompletion();
    }

    protected function detectText(Image $image): bool
    {
        // Simple text detection (adjust thresholds as needed)
        $edgeCount = 0;
        $pixels = $image->pickColor(0, 0, $image->width(), $image->height());

        // Count high-contrast edges (crude text indicator)
        foreach ($pixels as $row) {
            foreach ($row as $pixel) {
                if ($pixel['contrast'] > 0.3) { // Threshold adjustable
                    $edgeCount++;
                }
            }
        }

        return ($edgeCount / ($image->width() * $image->height())) > 0.15;
    }

    protected function calculateOptimalQuality(Image $image): int
    {
        // Quality matrix for JPEG
        return match(true) {
            ($image->width() > 1800 || $image->height() > 1800) => 82,
            ($image->width() < 800 && $image->height() < 800) => 75,
            default => 80
        };
    }

    protected function checkPostCompletion()
    {
        if ($this->postMedia->post->postmedias()
            ->where('status', '!=', 'compressed')
            ->doesntExist()) {
            $this->postMedia->post->update(['status' => 'active']);
        }
    }
}
