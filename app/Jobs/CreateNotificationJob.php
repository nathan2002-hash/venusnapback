<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\PostMedia;
use App\Models\UserSetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
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
        $notification = Notification::create([
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

        $this->sendPushNotification($notification);
    }

    protected function sendPushNotification($notification)
    {
        $receiverSettings = UserSetting::where('user_id', $this->targetUserId)->first();

        if (!$receiverSettings || !$receiverSettings->push_notifications || !$receiverSettings->fcm_token) {
            return;
        }

        $notificationData = $this->prepareNotificationData($notification);

        Http::withHeaders([
            'Authorization' => 'key=' . config('services.fcm.server_key'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $receiverSettings->fcm_token,
            'notification' => [
                'title' => $this->getNotificationTitle($notification->action),
                'body' => $this->getNotificationBody($notification),
                'sound' => 'default',
            ],
            'data' => $notificationData,
            'priority' => 'high',
        ]);
    }

    protected function prepareNotificationData($notification)
    {
        $data = json_decode($notification->data, true);
        $type = $this->determineTypeFromAction($notification->action);

        return [
            'type' => $type,
            'action' => $notification->action,
            'notifiable_id' => $notification->notifiable_id,
            'notifiablemedia_id' => $data['media_id'] ?? null,
            'metadata' => $data,
            'notification_id' => $notification->id,
        ];
    }

    protected function determineTypeFromAction($action)
    {
        $typeMap = [
            'viewed_album' => 'album_view',
            'commented' => 'comment',
            'replied' => 'comment',
            'liked' => 'post',
            'admired' => 'post',
            'shared_album' => 'album_request',
            'invited' => 'album_request',
        ];

        return $typeMap[$action] ?? 'post';
    }

    protected function getNotificationTitle($action)
    {
        $titles = [
            'viewed_album' => 'New Album View',
            'commented' => 'New Comment',
            'replied' => 'New Reply',
            'liked' => 'New Like',
            'admired' => 'New Admiration',
            'shared_album' => 'Album Invitation',
            'invited' => 'Collaboration Request',
        ];

        return $titles[$action] ?? 'New Notification';
    }

    protected function getNotificationBody($notification)
    {
        $data = json_decode($notification->data, true);
        $username = $data['username'] ?? 'Someone';
        $action = $notification->action;

        switch ($action) {
            case 'viewed_album':
                return "$username viewed your album";
            case 'commented':
                $albumName = $data['album_name'] ?? 'your post';
                return "$username commented on your post" . ($albumName ? " in $albumName" : "");
            case 'replied':
                $isAlbumOwner = $data['is_album_owner'] ?? false;
                $albumName = $data['album_name'] ?? null;
                return $isAlbumOwner ?
                    "$username replied to your comment in $albumName" :
                    "$username replied to your comment";
            case 'liked':
                return "$username liked your post";
            case 'admired':
                $albumName = $data['album_name'] ?? null;
                return $albumName ?
                    "$username admired your snap in $albumName" :
                    "$username admired your snap";
            case 'shared_album':
            case 'invited':
                $albumName = $data['album_name'] ?? 'an album';
                return "$username invited you to collaborate on $albumName";
            default:
                return "You have a new notification";
        }
    }

}
