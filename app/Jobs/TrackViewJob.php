<?php

namespace App\Jobs;

use App\Models\View;
use App\Models\PostMedia;
use App\Models\History;
use App\Models\User;
use App\Models\UserSetting;
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
        $postMedia = PostMedia::find($this->postMediaId);
        if (!$postMedia) return;

        View::create([
            'user_id' => $this->userId,
            'ip_address' => $this->ip,
            'post_media_id' => $this->postMediaId,
            'duration' => $this->duration,
            'user_agent' => $this->userAgent,
            'clicked' => false,
        ]);

        Recommendation::where('user_id', $this->userId)
            ->where('post_id', $postMedia->post_id)
            ->whereIn('status', ['active', 'fetched'])
            ->update(['status' => 'seen']);

        $user = User::find($this->userId);
        if (!$user) return;

        // Fetch user settings only (do not create default)
        $settings = UserSetting::where('user_id', $this->userId)->first();

        if (!$settings || !$settings->history) {
            return;
        }

        History::create([
            'user_id' => $this->userId,
            'post_id' => $postMedia->post_id,
            'ip_address' => $this->ip,
            'clicked' => false, // or true if this is a click
            'user_agent' => $this->userAgent,
            'device_info' => null, // optional: add device info if you have it
        ]);

    }
}
