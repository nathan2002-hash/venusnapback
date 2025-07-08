<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\WebpEncoder;

class ProfileUpdate implements ShouldQueue
{
    use Queueable;
    public $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $manager = new ImageManager(new GdDriver());

        if ($this->user->profile_original) {
            $originalImageprofile = Storage::disk('s3')->get($this->user->profile_original);
            $imageprofile = $manager->read($originalImageprofile);
            $compressedImageprofile = $imageprofile->encode(new WebpEncoder(quality: 50));

            $compressedPathprofile = 'uploads/profiles/compressed/profile/' . basename($this->user->profile_original);
            Storage::disk('s3')->put($compressedPathprofile, (string) $compressedImageprofile);

            $this->user->profile_compressed = $compressedPathprofile;
        }

        if ($this->user->cover_original) {
            $originalImagecover = Storage::disk('s3')->get($this->user->cover_original);
            $imagecover = $manager->read($originalImagecover);
            $compressedImagecover = $imagecover->encode(new WebpEncoder(quality: 60));

            $compressedPathcover = 'uploads/profiles/compressed/cover/' . basename($this->user->cover_original);
            Storage::disk('s3')->put($compressedPathcover, (string) $compressedImagecover);

            $this->user->cover_compressed = $compressedPathcover;
        }

        $this->user->save();
    }

}
