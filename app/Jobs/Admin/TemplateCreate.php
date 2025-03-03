<?php

namespace App\Jobs\Admin;

use App\Models\Template;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Facades\Storage;

class TemplateCreate implements ShouldQueue
{
    use Queueable;
    public $templateId;

    /**
     * Create a new job instance.
     */
    public function __construct($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $template = Template::find($this->templateId);
        if (!$template || !$template->original_template) return; // Handle edge cases gracefully

        $path = $template->original_template;
        $originalImage = Storage::disk('s3')->get($path);

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($originalImage);
        $compressedImage = $image->encode(new WebpEncoder(quality: 75));

        $compressedPath = 'uploads/templates/compressed/' . basename($path);
        Storage::disk('s3')->put($compressedPath, (string) $compressedImage);

        $template->update(['compressed_template' => $compressedPath]);
    }
}
