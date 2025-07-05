<?php

namespace App\Jobs;

use App\Models\AdMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class AdImageCompress implements ShouldQueue
{
    use Queueable;
    public $media;

    /**
     * Create a new job instance.
     */
    public function __construct(AdMedia $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $path = $this->media->file_path;
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);

        // Set a max width for resizing while keeping aspect ratio
        $maxWidth = 2000; // Adjust based on Venusnap's needs

        // Resize only if the image is larger than maxWidth
        if ($image->width() > $maxWidth) {
            $image = $image->scale(width: $maxWidth);
        }

        // Compress to WebP with a lower quality for better performance
        $compressedImage = $image->encode(new WebpEncoder(quality: 65)); // Reduce quality for smaller file size

        // Store compressed image
        $compressedPath = 'ads/media/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        // Update media record
        $this->media->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);

        // Check if all media for the post are compressed
        $ad = $this->media->post; // Assuming PostMedia belongsTo Post

        $allCompressed = $ad->media()->where('status', '!=', 'compressed')->doesntExist();

        if ($allCompressed) {
            $ad->update(['status' => 'active']);
        }
    }
}
