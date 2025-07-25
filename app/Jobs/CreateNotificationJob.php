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
use Illuminate\Support\Facades\Storage;

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
           // Get JSON string from env
            $jsonContent = env('FIREBASE_CREDENTIALS_JSON');

            if ($jsonContent === false || empty($jsonContent)) {
                throw new \Exception('Failed to fetch Firebase credentials');
            }

            // Create temporary credentials file with JSON string
            $tempFilePath = tempnam(sys_get_temp_dir(), 'firebase_cred_');
            file_put_contents($tempFilePath, $jsonContent);

            // Initialize Firebase with temp file path
            $factory = (new Factory)->withServiceAccount($tempFilePath);
            $messaging = $factory->createMessaging();

            // Prepare notification data
            $title = $this->getNotificationTitle($notification);
            $body = $this->getNotificationBody($notification);
            $notificationData = $this->preparePushData($notification);

            $imageUrl = $notificationData['image'] ?? null;
            unset($notificationData['image']); // Remove from data payload

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
                    if ($imageUrl) {
                        $androidConfig = [
                            'notification' => [
                                'image' => $imageUrl
                            ]
                        ];
                        $message = $message->withAndroidConfig($androidConfig);
                    }

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

        $imageUrl = $this->getNotificationImageUrl($notification, $data);

        // For media-specific actions, include the post_id in metadata
        if (in_array($notification->action, ['admired', 'liked', 'commented', 'replied'])) {
            if ($notification->notifiable_type === 'App\Models\PostMedia') {
                $data['post_id'] = $this->notifiable->post_id;
            }
        }

        // Ensure all metadata values are strings
        $metadata = [];
        foreach ($data as $key => $value) {
            $metadata[$key] = is_array($value) ? json_encode($value) : (string)$value;
        }

        return [
            'type' => $type,
            'action' => $notification->action,
            'notifiable_id' => (string)$notification->notifiable_id, // Could be post_media_id or album_id
            'notifiablemedia_id' => $data['media_id'] ?? '0',
            'screen_to_open' => $this->getTargetScreen($notification->action),
            'metadata' => $this->sanitizeData($data),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'image' => $imageUrl,
            //'is_big_picture' => 'false',
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

    protected function getNotificationImageUrl($notification, $data)
    {
        // For album-related notifications
        if (in_array($notification->action, ['viewed_album', 'shared_album', 'invited'])) {
            $album = $this->notifiable;

            if ($album->type === 'personal' || $album->type === 'creator') {
                return $album->thumbnail_compressed
                    ? generateSecureMediaUrl($album->thumbnail_compressed)
                    : ($album->thumbnail_original
                        ? generateSecureMediaUrl($album->thumbnail_original)
                        : null);
            } elseif ($album->type === 'business') {
                return $album->business_logo_compressed
                    ? generateSecureMediaUrl($album->business_logo_compressed)
                    : ($album->business_logo_original
                        ? generateSecureMediaUrl($album->business_logo_original)
                        : null);
            }
        }

        // For user-related notifications (likes, comments, etc.)
        $sender = User::find($data['sender_id'] ?? null);
        if ($sender) {
            return $sender->profile_compressed
                ? generateSecureMediaUrl($sender->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($sender->email))) . '?s=100&d=mp';
        }

        return null;
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

    protected function getNotificationTitle($notification)
    {
        $data = json_decode($notification->data, true);
        return $data['username'] ?? 'Someone';
    }

    protected function getNotificationBody($notification)
    {
        $data = json_decode($notification->data, true);
        $action = $notification->action;

        switch ($action) {
            case 'viewed_album':
                $albumName = $data['album_name'] ?? 'your album';
                return "explored your $albumName Album";
            case 'commented':
                $albumName = $data['album_name'] ?? null;
                return $albumName ?
                    "commented on your post in $albumName Album" :
                    "commented on your post";
            case 'replied':
                $isAlbumOwner = $data['is_album_owner'] ?? false;
                $albumName = $data['album_name'] ?? null;
                return $isAlbumOwner ?
                    "replied to your comment in $albumName Album" :
                    "replied to your comment";
            case 'liked':
                return "liked your post";
            case 'admired':
                $albumName = $data['album_name'] ?? null;
                return $albumName ?
                    "admired your snap in $albumName Album" :
                    "admired your snap";
            case 'shared_album':
            case 'invited':
                $albumName = $data['album_name'] ?? 'an album';
                return "invited you to collaborate on $albumName Album";
            default:
                return "sent you a notification";
        }
    }
}
