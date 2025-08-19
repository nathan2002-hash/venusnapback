<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\History;
use App\Models\View;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogPostMediaView implements ShouldQueue
{
    use Queueable;
    protected $postId;
    protected $userId;
    protected $ipAddress;
    protected $userAgent;
    protected $deviceInfo;
    protected $duration;
    protected $clicked;
    /**
     * Create a new job instance.
     */
    public function __construct($postId, $userId, $ipAddress, $userAgent, $deviceInfo, $duration = 0, $clicked = false)
    {
        $this->postId = $postId;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->deviceInfo = $deviceInfo;
        $this->duration = $duration;
        $this->clicked = $clicked;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Find the post
        $post = Post::with('postmedias')->where('id', $this->postId)->first();

        if (!$post) {
            return; // Stop execution if post is not found
        }

        // Find the first postmedia with sequence_order = 1
        $postMedia = $post->postmedias->where('sequence_order', 1)->first();

        if (!$postMedia) {
            return; // Stop execution if no postmedia with sequence_order = 1
        }

        // Create the view record
        View::create([
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'duration' => $this->duration,
            'post_media_id' => $postMedia->id,
            'user_agent' => $this->userAgent,
            'device_info' => $this->deviceInfo,
            'clicked' => $this->clicked,
        ]);

        History::create([
            'user_id' => $this->userId,
            'post_id' => $postMedia->post_id,
            'ip_address' => $this->ipAddress,
            'clicked' => $this->clicked, // or true if this is a click
            'user_agent' => $this->userAgent,
            'device_info' => $this->deviceInfo, // optional: add device info if you have it
        ]);
    }
}
