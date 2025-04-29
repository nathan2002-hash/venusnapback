<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\GenAi;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;

class CompressGenAiImage implements ShouldQueue
{
    use Queueable;

    public $genaiId;

    /**
     * Create a new job instance.
     */
    public function __construct($genaiId)
    {
        $this->genaiId = $genaiId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $genai = GenAi::findOrFail($this->genaiId);
        $path = $genai->file_path;

        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);

        $maxWidth = 1200;

        if ($image->width() > $maxWidth) {
            $image = $image->scale(width: $maxWidth);
        }

        $compressedImage = $image->encode(new WebpEncoder(quality: 60));
        $compressedPath = 'genai/compressed/' . pathinfo($path, PATHINFO_FILENAME) . '.webp';

        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        // Mark genai record as completed after compression
        $genai->update([
            'file_path_compress' => $compressedPath,
            'status' => 'completed',
        ]);
    }
}
