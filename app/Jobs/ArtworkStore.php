<?php

namespace App\Jobs;

use App\Models\Artwork;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ArtworkStore implements ShouldQueue
{
    use Queueable;
    public $artworkId;

    /**
     * Create a new job instance.
     */
    public function __construct($artworkId)
    {
        $this->artworkId = $artworkId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $artwork = Artwork::find($this->artworkId);
        if (!$artwork || !$artwork->file_path) {
            return; // Handle edge cases gracefully
        }

        $path = $artwork->file_path;

        // Direct binary content
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());

        // Read binary directly
        $image = $manager->read($originalImage);

        $compressedImage = $image->encode(new WebpEncoder(quality: 75));

        $compressedPath = 'uploads/artworks/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $artwork->update(['thumbnail' => $compressedPath]);
    }
}
