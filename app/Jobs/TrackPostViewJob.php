<?php

namespace App\Jobs;

use App\Models\View;
use App\Models\PostMedia;
use App\Models\History;
use App\Models\Recommendation;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrackPostViewJob implements ShouldQueue
{
    use Queueable;

    public $userId, $ip, $postId, $postMediaId, $duration, $userAgent;

    public function __construct($userId, $ip, $postId, $postMediaId, $duration, $userAgent)
    {
        $this->userId = $userId;
        $this->ip = $ip;
        $this->postId = $postId;
        $this->postMediaId = $postMediaId;
        $this->duration = $duration;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $postMediaId = $this->postMediaId;

        if (!$postMediaId && $this->postId) {
            $media = PostMedia::where('post_id', $this->postId)
                ->where('sequence_order', 1)
                ->first();

            if ($media) {
                $postMediaId = $media->id;
            }
        }

        if ($postMediaId) {
            View::create([
                'user_id' => $this->userId,
                'ip_address' => $this->ip,
                'post_media_id' => $postMediaId,
                'duration' => $this->duration,
                'user_agent' => $this->userAgent,
                'clicked' => false,
            ]);

            History::create([
                'user_id' => $this->userId,
                'post_id' => $this->postId,
                'ip_address' => $this->ip,
                'clicked' => false, // or true if this is a click
                'user_agent' => $this->userAgent,
                'device_info' => null, // optional: add device info if you have it
            ]);
        }

        Recommendation::where('user_id', $this->userId)
            ->where('post_id', $this->postId)
            ->whereIn('status', ['active', 'fetched'])
            ->update(['status' => 'seen']);
    }
}
