<?php

namespace App\Jobs;

use App\Models\PostMedia;
use Intervention\Image\Image;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\WebpEncoder;

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
        $path = $this->postMedia->file_path;
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);

        // Set a max width for resizing while keeping aspect ratio
        $maxWidth = 1200; // Adjust based on Venusnap's needs

        // Resize only if the image is larger than maxWidth
        if ($image->width() > $maxWidth) {
            $image = $image->scale(width: $maxWidth);
        }

        // Compress to WebP with a lower quality for better performance
        $compressedImage = $image->encode(new WebpEncoder(quality: 60)); // Reduce quality for smaller file size

        // Store compressed image
        $compressedPath = 'uploads/posts/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        // Update media record
        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);

        // Check if all media for the post are compressed
        $post = $this->postMedia->post; // Assuming PostMedia belongsTo Post

        $allCompressed = $post->postmedias()->where('status', '!=', 'compressed')->doesntExist();

        if ($allCompressed) {
            $post->update(['status' => 'active']);
        }
    }
}
