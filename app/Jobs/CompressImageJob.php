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
            $path = $this->postMedia->file_path;
            $originalImage = Storage::disk('s3')->get($path);

            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($originalImage);

            // Optimization parameters
            $maxWidth = 1200;
            $jpegQuality = 50; // Optimal balance for JPEG
            $webpQuality = 50; // WebP can handle higher quality at smaller sizes

            // Resize logic
            if ($image->width() > $maxWidth) {
                $image = $image->scale(width: $maxWidth);
            }

            // Generate both WebP and JPEG versions
            $webpImage = $image->encode(new WebpEncoder(quality: $webpQuality));
            $jpegImage = $image->encode(new JpegEncoder(quality: $jpegQuality, progressive: true));
            //$jpegImage = $image->encode(new JpegEncoder(quality: $jpegQuality));

            // Generate unique filenames
            $filename = pathinfo($path, PATHINFO_FILENAME);
            $webpPath = "uploads/posts/compressed/{$filename}.webp";
            //$jpegPath = "uploads/posts/compressed/{$filename}.jpg";

            // Store compressed versions
            Storage::disk('s3')->put($webpPath, (string) $webpImage);
            //Storage::disk('s3')->put($jpegPath, (string) $jpegImage);

            // Update media record with both formats
            $this->postMedia->update([
                'status' => 'compressed',
                'file_path_compress' => $webpPath,
                //'file_path_jpg' => $jpegPath,
                // 'original_filesize' => strlen($originalImage),
                // 'compressed_filesize' => min(strlen((string) $webpImage), strlen((string) $jpegImage)),
            ]);

            $this->checkPostCompletion();

        } catch (\Exception $e) {
            Log::error("Image compression failed for media {$this->postMedia->id}: " . $e->getMessage());
            $this->postMedia->update(['status' => 'failed']);
        }
    }

    protected function checkPostCompletion()
    {
        $post = $this->postMedia->post;

        if ($post->postmedias()->where('status', '!=', 'compressed')->doesntExist()) {
            $post->update(['status' => 'review']);
        }

        if ($post->visibility === 'Private') {
            return;
        }

        $numbers = range(0, 9);
        $first = $numbers[array_rand($numbers)];
        $second = $numbers[array_rand($numbers)];

        // if ($first === $second) {
        //     return;
        // }

        $randomMedia = $post->postmedias()->inRandomOrder()->first();
        $album = $post->album;

        CreateNotificationJob::dispatch(
            $post->user, // sender
            $post,      // notifiable
            'album_new_post', // action
            null,       // targetUserId will be set per supporter
            [],         // data
            true,       // isBigPicture
            $post,      // post
            $album,     // album
            $randomMedia // randomMedia
        )->delay(now()->addSeconds(30)); // Small buffer
    }
}
