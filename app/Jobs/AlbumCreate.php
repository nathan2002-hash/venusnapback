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

        if (!$album) return; // Album not found

        $manager = new ImageManager(new GdDriver());

        if ($album->type === 'personal' || $album->type === 'creator') {
            if (!$album->thumbnail_original) return;

            $this->compressAndStore($album, 'thumbnail_original', 'thumbnail_compressed', $manager);
        }

        if ($album->type === 'business') {
            // Process business logo
            if ($album->business_logo_original) {
                $this->compressAndStore($album, 'business_logo_original', 'business_logo_compressed', $manager);
            }

            // Process cover image
            if ($album->cover_image_original) {
                $this->compressAndStore($album, 'cover_image_original', 'cover_image_compressed', $manager);
            }
        }
    }

    protected function compressAndStore(Album $album, string $originalKey, string $compressedKey, ImageManager $manager)
    {
        $path = $album->{$originalKey};

        $originalImage = Storage::disk('s3')->get($path);
        $image = $manager->read($originalImage);

        $compressedImage = $image->encode(new WebpEncoder(quality: 50));

        $compressedPath = 'uploads/albums/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $album->update([$compressedKey => $compressedPath]);
    }

}
