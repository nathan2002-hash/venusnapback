<?php

namespace App\Jobs;

use App\Models\Album;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;

class AlbumCreate implements ShouldQueue
{
    use Queueable;
    public $albumId;

    /**
     * Create a new job instance.
     */
    public function __construct($albumId)
    {
        $this->albumId = $albumId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $album = Album::find($this->albumId);
        if (!$album || !$album->original_cover) return; // Handle edge cases gracefully

        $path = $album->original_cover;
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);
        $compressedImage = $image->encode(new WebpEncoder(quality: 75));

        $compressedPath = 'uploads/albums/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $album->update(['compressed_cover' => $compressedPath]);
    }

}
