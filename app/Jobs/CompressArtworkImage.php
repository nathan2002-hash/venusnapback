<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use App\Models\Artwork;

class CompressArtworkImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $artworkId) {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $artwork = Artwork::findOrFail($this->artworkId);
        $path = $artwork->file_path;

        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($originalImage);

        // Resize to a maximum width of 800px maintaining aspect ratio
        $image->scale(width: 800);

        $compressedImage = $image->encode(new WebpEncoder(quality: 75));
        $compressedPath = 'uploads/artworks/compressed/' . pathinfo($path, PATHINFO_FILENAME) . '.webp';

        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        // Mark artwork as completed
        $artwork->update([
            'thumbnail' => $compressedPath,
            'status' => 'completed',
        ]);
    }
}
