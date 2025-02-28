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

        // Create image manager using GD driver
        $manager = new ImageManager(new GdDriver());

        // Read the image from binary string (v3 uses `read()`, not `make()`)
        $image = $manager->read($originalImage);

        // Encode to WebP using new encoder class
        $compressedImage = $image->encode(new WebpEncoder(quality: 75));

        // Store compressed image
        $compressedPath = 'uploads/posts/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        // Update database record
        $this->postMedia->update([
            'status' => 'compressed',
            'file_path_compress' => $compressedPath,
        ]);
    }
}
