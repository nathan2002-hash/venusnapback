<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Activity;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class LoginActivityJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $status;
    protected $description;
    protected $title;
    protected $userAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, bool $status, string $description, string $title, string $userAgent)
    {
        $this->user = $user;
        $this->status = $status;
        $this->description = $description;
        $this->title = $title;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activity = new Activity();
        $activity->title = $this->title;
        $activity->description = $this->description;
        $activity->source = 'Authentication';
        $activity->user_id = $this->user->id;
        $activity->status = $this->status;
        $activity->user_agent = $this->userAgent;
        $activity->save();
    }
}
