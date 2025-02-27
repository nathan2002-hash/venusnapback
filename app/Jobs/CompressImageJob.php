<?php

namespace App\Jobs;

use App\Models\PostMedia;
use Intervention\Image\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        // Get the path to the original image
        $path = $this->postMedia->file_path;
        $originalImage = Storage::disk('s3')->get($path);

        // Create a new image instance
        $image = Image::make($originalImage);

        // Compress the image (adjust quality as needed)
        $compressedImage = $image->encode('webp', 75);  // Compress to webp with 75% quality

        // Store the compressed image in a different folder
        $compressedPath = 'uploads/posts/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, $compressedImage);

        // Update the database record
        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);
    }
}
