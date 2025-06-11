<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\PostMedia;
use App\Models\UserSetting;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Notification as NotificationModel;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

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
        try {
            // Check user settings first
            $settings = UserSetting::where('user_id', $this->targetUserId)->first();

            if (!$settings || !$settings->push_notifications) {
                // User has disabled push notifications; skip sending
                return;
            }

            // Store the notification
            $notificationData = [
                'user_id' => $this->targetUserId,
                'action' => $this->action,
                'notifiable_type' => get_class($this->notifiable),
                'notifiable_id' => $this->notifiable->id,
                'data' => $this->prepareNotificationDataForStorage(),
                'group_count' => 0,
                'is_read' => false,
            ];

            $notification = NotificationModel::create($notificationData);

            // Now send the push notification
            $this->sendPushNotification($notification);

        } catch (\Exception $e) {
            Log::error('Notification creation failed: ' . $e->getMessage(), [
                'target_user' => $this->targetUserId,
                'action' => $this->action,
                'error' => $e->getTraceAsString()
            ]);
        }
    }



    protected function sanitizeData(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            // Convert other non-scalar values to strings if needed
            elseif (!is_scalar($value)) {
                $value = (string)$value;
            }
        });
        return $data;
    }

    protected function prepareNotificationDataForStorage(): string
    {
        $baseData = [
            'username' => $this->sender->name,
            'sender_id' => $this->sender->id,
        ];

        $mergedData = array_merge($baseData, $this->data);
        return json_encode($mergedData, JSON_UNESCAPED_UNICODE);
    }

    protected function sendPushNotification($notification)
    {
        // Get all active FCM tokens for the target user
        $activeTokens = FcmToken::where('user_id', $this->targetUserId)
                            ->where('status', 'active')
                            ->pluck('token')
                            ->toArray();

        if (empty($activeTokens)) {
            return;
        }

        try {
            // Download Firebase credentials
            $jsonContent = file_get_contents('https://venusnaplondon.s3.eu-west-2.amazonaws.com/system/venusnap-d5340-firebase-adminsdk-fbsvc-b55072fb51.json');

            if ($jsonContent === false) {
                throw new \Exception('Failed to fetch Firebase credentials');
            }

            // Create temporary credentials file
            $tempFilePath = tempnam(sys_get_temp_dir(), 'firebase_cred_');
            file_put_contents($tempFilePath, $jsonContent);

            // Initialize Firebase
            $factory = (new Factory)->withServiceAccount($tempFilePath);
            $messaging = $factory->createMessaging();

            // Prepare notification data
            $title = $this->getNotificationTitle($notification->action);
            $body = $this->getNotificationBody($notification);
            $notificationData = $this->preparePushData($notification);

            // Ensure all data values are strings
            $stringData = [];
            foreach ($notificationData as $key => $value) {
                if (is_array($value)) {
                    $stringData[$key] = json_encode($value);
                } else {
                    $stringData[$key] = (string)$value;
                }
            }

            // Send to each active token
            foreach ($activeTokens as $token) {
                try {
                    $message = CloudMessage::withTarget('token', $token)
                        ->withNotification(FirebaseNotification::create($title, $body))
                        ->withHighestPossiblePriority()
                        ->withData($stringData);

                    $messaging->send($message);
                } catch (\Exception $e) {
                    Log::error('Failed to send to token: ' . $token, [
                        'error' => $e->getMessage(),
                        'user_id' => $this->targetUserId
                    ]);

                    // Mark failed token as expired
                    FcmToken::where('token', $token)->update(['status' => 'expired']);
                }
            }

        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage(), [
                'user_id' => $this->targetUserId,
                'notification_data' => $notification->data,
                'error' => $e->getTraceAsString()
            ]);
        } finally {
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

    protected function preparePushData($notification): array
    {
        $data = json_decode($notification->data, true);
        $type = $this->determineTypeFromAction($notification->action);

        // Ensure all metadata values are strings
        $metadata = [];
        foreach ($data as $key => $value) {
            $metadata[$key] = is_array($value) ? json_encode($value) : (string)$value;
        }

         return [
            'type' => $this->determineTypeFromAction($notification->action),
            'action' => $notification->action,
            'notifiable_id' => (string)$notification->notifiable_id,
            'notifiablemedia_id' => $data['media_id'] ?? '0',
            'screen_to_open' => $this->getTargetScreen($notification->action),
            'metadata' => $this->sanitizeData($data),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK', // Critical
        ];
    }

    protected function getTargetScreen($action): string
    {
        return match($action) {
            'viewed_album' => 'album_analytics',
            'commented', 'replied', 'liked', 'admired' => 'post',
            'shared_album', 'invited' => 'album_requests',
            default => 'notifications'
        };
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
