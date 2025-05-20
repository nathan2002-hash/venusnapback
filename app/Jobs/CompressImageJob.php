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
use Log;

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

    public function handle()
    {
        try {
            $path = $this->postMedia->file_path;
            $originalImage = Storage::disk('s3')->get($path);

            $manager = new ImageManager(new GdDriver());

            // Validate image before processing
            if (!$this->isValidImage($originalImage)) {
                throw new \Exception("Invalid image file");
            }

            $image = $manager->read($originalImage);

            // Handle animated GIFs differently
            if ($this->isAnimatedGif($originalImage)) {
                return $this->handleAnimatedImage($path, $originalImage);
            }

            // Detect if image contains text
            $hasText = $this->detectText($image);

            // Smart resizing
            $maxDimension = $hasText ? 1600 : 2000;
            $targetDimension = $hasText ? 1000 : 1200;

            if ($image->width() > $maxDimension || $image->height() > $maxDimension) {
                $image->scaleDown($maxDimension, $maxDimension);
            } elseif ($image->width() > $targetDimension || $image->height() > $targetDimension) {
                $image->scaleDown($targetDimension, $targetDimension);
            }

            // Format selection
            if ($hasText) {
                $compressedImage = $image->encode(new PngEncoder());
                $extension = 'png';
            } else {
                $compressedImage = $image->encode(new JpegEncoder(
                    quality: $this->calculateOptimalQuality($image),
                    progressive: true
                ));
                $extension = 'jpg';
            }

            $this->saveCompressedImage($path, $extension, $compressedImage);

        } catch (\Exception $e) {
            Log::error("Image compression failed: " . $e->getMessage());
            $this->postMedia->update(['status' => 'failed']);
        }
    }

    protected function isValidImage($imageData): bool
    {
        return @imagecreatefromstring($imageData) !== false;
    }

    protected function isAnimatedGif($imageData): bool
    {
        return strpos($imageData, "\x00\x21\xF9\x04") !== false;
    }

    protected function handleAnimatedImage($path, $imageData)
    {
        // For animated GIFs, we'll just copy the original
        $compressedPath = 'uploads/posts/compressed/' . pathinfo($path, PATHINFO_FILENAME) . '_opt.gif';
        Storage::disk('s3')->put($compressedPath, $imageData);

        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);
    }

   protected function detectText(Image $image): bool
{
    // Sample every 10 pixels
    $sampleSize = 10;
    $edgeCount = 0;
    $totalPixels = 0;

    for ($y = 0; $y < $image->height(); $y += $sampleSize) {
        for ($x = 0; $x < $image->width(); $x += $sampleSize) {
            try {
                /** @var \Intervention\Image\Colors\Rgb\Color $pixel */
                $pixel = $image->pickColor($x, $y);

                $red = $pixel->red();
                $green = $pixel->green();
                $blue = $pixel->blue();

                $contrast = max($red, $green, $blue) - min($red, $green, $blue);

                if ($contrast > 76) {
                    $edgeCount++;
                }

                $totalPixels++;
            } catch (\Throwable $e) {
                Log::warning("Color sampling failed at ($x, $y): " . $e->getMessage());
            }
        }
    }

    return ($totalPixels > 0) && (($edgeCount / $totalPixels) > 0.15);
}



    protected function saveCompressedImage($originalPath, $extension, $imageData)
    {
        $compressedPath = 'uploads/posts/compressed/' . pathinfo($originalPath, PATHINFO_FILENAME) . '_opt.' . $extension;
        Storage::disk('s3')->put($compressedPath, (string) $imageData);

        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);

        $this->checkPostCompletion();
    }

    protected function checkPostCompletion()
    {
        if ($this->postMedia->post->postmedias()
            ->where('status', '!=', 'compressed')
            ->doesntExist()) {
            $this->postMedia->post->update(['status' => 'active']);
        }
    }

    protected function calculateOptimalQuality(Image $image): int
    {
        // Base quality matrix
        return match(true) {
            ($image->width() > 1800 || $image->height() > 1800) => 90, // Large images get higher quality
            ($image->width() < 800 && $image->height() < 800) => 80,   // Small images can tolerate more compression
            default => 85                                             // Default balanced quality
        };
    }
}
