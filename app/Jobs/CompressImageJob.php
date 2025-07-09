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
    public function handle()
    {
        try {
            // Fetch ALL pending post media
            $pendingMedias = \App\Models\PostMedia::where('status', 'pending')->get();

            if ($pendingMedias->isEmpty()) {
                Log::info("No pending post media found.");
                return;
            }

            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            foreach ($pendingMedias as $media) {
                try {
                    $path = $media->file_path;
                    $originalImage = \Storage::disk('s3')->get($path);
                    $image = $manager->read($originalImage);

                    // Resize
                    $maxWidth = 1200;
                    if ($image->width() > $maxWidth) {
                        $image = $image->scale(width: $maxWidth);
                    }

                    // Encode images
                    $webpImage = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(quality: 35));
                    $jpegImage = $image->encode(new \Intervention\Image\Encoders\JpegEncoder(quality: 50, progressive: true));

                    // Paths
                    $filename = pathinfo($path, PATHINFO_FILENAME);
                    $webpPath = "uploads/posts/compressed/{$filename}.webp";
                    $jpegPath = "uploads/posts/compressed/{$filename}.jpg";

                    // Store in S3
                    \Storage::disk('s3')->put($webpPath, (string) $webpImage);
                    \Storage::disk('s3')->put($jpegPath, (string) $jpegImage);

                    // Update record
                    $media->update([
                        'status' => 'compressed',
                        'file_path_compress' => $webpPath,
                        'file_path_jpg' => $jpegPath,
                    ]);

                    // Check if all post media are compressed
                    //$this->checkPostCompletion($media);

                    Log::info("Compressed post media ID {$media->id}");

                } catch (\Exception $e) {
                    Log::error("Failed to compress post media ID {$media->id}: " . $e->getMessage());
                    $media->update(['status' => 'failed']);
                }
            }

        } catch (\Exception $e) {
            Log::error("Compression batch failed: " . $e->getMessage());
        }
    }


    // protected function checkPostCompletion()
    // {
    //     $post = $this->postMedia->post;

    //     if ($post->postmedias()->where('status', '!=', 'compressed')->doesntExist()) {
    //         $post->update(['status' => 'review']);
    //     }
    // }
}
