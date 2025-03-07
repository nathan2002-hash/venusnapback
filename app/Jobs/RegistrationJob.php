<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Album;
use App\Models\Activity;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegistrationJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $userAgent;

    public function __construct(User $user, $userAgent)
    {
        $this->user = $user;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $randomNumber = mt_rand(1000, 9999);

        // $album = new Album();
        // $album->name = $this->user->name . $randomNumber;
        // $album->description = "This is " . $this->user->name . "'s Album";
        // $album->user_id = $this->user->id;
        // $album->type = "general";
        // $album->status = 'active';
        // $album->is_verified = 0;
        // $album->visibility = 'public';
        // $album->save();

        $activity = new Activity();
        $activity->title = 'Account Created';
        $activity->description = 'Your account has been created';
        $activity->source = 'Registration';
        $activity->user_id = $this->user->id;
        $activity->status = true;
        $activity->user_agent = $this->userAgent;
        $activity->save();
    }
}
