<?php

namespace App\Jobs;

use App\Models\View;
use App\Models\PostMedia;
use App\Models\Recommendation;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrackViewJob implements ShouldQueue
{
    use Queueable;

    public $userId, $ip, $postMediaId, $duration, $userAgent;

    public function __construct($userId, $ip, $postMediaId, $duration, $userAgent)
    {
        $this->userId = $userId;
        $this->ip = $ip;
        $this->postMediaId = $postMediaId;
        $this->duration = $duration;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        View::create([
            'user_id' => $this->userId,
            'ip_address' => $this->ip,
            'post_media_id' => $this->postMediaId,
            'duration' => $this->duration,
            'user_agent' => $this->userAgent,
            'clicked' => false,
        ]);

        $postMedia = PostMedia::find($this->postMediaId);
        if (!$postMedia) return;

        Recommendation::where('user_id', $this->userId)
            ->where('post_id', $postMedia->post_id)
            ->whereIn('status', ['active', 'fetched'])
            ->update(['status' => 'seen']);
    }
}
