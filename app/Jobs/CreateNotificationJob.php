<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\PostMedia;
use App\Models\Notification;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateNotificationJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $postMediaId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $postMediaId)
    {
        $this->user = $user;
        $this->postMediaId = $postMediaId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $postMedia = PostMedia::find($this->postMediaId);
        if (!$postMedia) {
            return;
        }

        $post = Post::find($postMedia->post_id);

        // Create a notification for the like action
        Notification::create([
            'user_id' => $post->user_id, // The user receiving the notification (post owner)
            'action' => 'admired', // Action type (can be 'liked', 'admired', etc.)
            'notifiable_type' => PostMedia::class, // The model being interacted with
            'notifiable_id' => $postMedia->id, // The post media ID
            'data' => json_encode([
                'username' => $this->user->name, // The name of the user admiring the post
                'sender_id' => $this->user->id,
            ]),
            'group_count' => 0, // Will be updated later when grouped
            'is_read' => false, // Notification is unread initially
        ]);
    }
}
