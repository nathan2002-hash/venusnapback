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

    protected $sender;
    protected $notifiable;
    protected $action;
    protected $targetUserId;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct(User $sender, $notifiable, $action, $targetUserId, array $data = [])
    {
        $this->sender = $sender;
        $this->notifiable = $notifiable;
        $this->action = $action;
        $this->targetUserId = $targetUserId;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::create([
            'user_id' => $this->targetUserId,
            'action' => $this->action,
            'notifiable_type' => get_class($this->notifiable),
            'notifiable_id' => $this->notifiable->id,
            'data' => json_encode(array_merge([
                'username' => $this->sender->name,
                'sender_id' => $this->sender->id,
            ], $this->data)),
            'group_count' => 0,
            'is_read' => false,
        ]);
    }
}
