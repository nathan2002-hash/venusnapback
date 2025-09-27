<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\Album;
use App\Models\SystemError;
use App\Models\UserSetting;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Notification as NotificationModel;
use App\Models\NotificationDelivery;
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
    protected $isBigPicture;
    protected $post;
    protected $album;
    protected $randomMedia;

    /**
     * Create a new job instance.
     */
    public function __construct(
        User $sender,
        $notifiable,
        $action,
        $targetUserId = null,
        array $data = [],
        $isBigPicture = false,
        $post = null,
        $album = null,
        $randomMedia = null
    ) {
        $this->sender = $sender;
        $this->notifiable = $notifiable;
        $this->action = $action;
        $this->targetUserId = $targetUserId;
        $this->data = $data;
        $this->isBigPicture = $isBigPicture;
        $this->post = $post;
        $this->album = $album;
        $this->randomMedia = $randomMedia;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->isBigPicture) {
                $this->handleBigPictureNotification();
            } else {
                // Only check settings for regular notifications
                $settings = UserSetting::where('user_id', $this->targetUserId)->first();
                if (!$settings || !$settings->push_notifications) {
                    return;
                }
                $this->handleRegularNotification();
            }
        } catch (\Exception $e) {
            Log::error('Notification creation failed: ' . $e->getMessage(), [
                'target_user' => $this->targetUserId,
                'action' => $this->action,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    protected function handleRegularNotification()
    {
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

        $deliveryId = $this->createDeliveryRecord($notification);

        // Now send the push notification
        $this->sendPushNotification($notification, $deliveryId);
    }

    protected function handleBigPictureNotification()
{
    // Get supporters without eager loading
    $supporters = $this->album->supporters()->get();

    foreach ($supporters as $supporter) {
        // Explicitly load the user relationship
        $user = $supporter->user;

        if (!$user || $user->id == $this->post->user_id) {
            continue;
        }

        // Get user settings (single query per user)
        $settings = UserSetting::where('user_id', $user->id)->first();
        if (!$settings || !$settings->push_notifications) {
            continue;
        }

        // Check for active FCM tokens (single query per user)
        $activeTokens = FcmToken::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if (!$activeTokens) {
            continue;
        }

        // Create notification
        $notification = NotificationModel::create([
            'user_id' => $user->id,
            'action' => 'album_new_post',
            'notifiable_type' => get_class($this->post),
            'notifiable_id' => $this->post->id,
            'data' => json_encode([
                'username' => $this->post->user->name,
                'sender_id' => $this->post->user_id,
                'album_name' => $this->album->name,
                'post_id' => $this->post->id,
                'media_id' => $this->randomMedia->id,
                'image' => generateSecureMediaUrl($this->randomMedia->file_path_compress)
            ]),
            'is_read' => false
        ]);

         $deliveryId = $this->createDeliveryRecord($notification);

        $this->sendBigPicturePushNotification($user, $notification, $deliveryId);
    }
}

protected function createDeliveryRecord($notification)
    {
        $delivery = NotificationDelivery::create([
            'notification_id' => $notification->id,
            'user_id' => $notification->user_id,
            'status' => 'sent',
            'sent_at' => now(),
            'message_id' => 'msg_' . uniqid() . '_' . time(), // Unique message ID
        ]);

        return $delivery->id;
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

    protected function sendPushNotification($notification, $deliveryId)
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

            $signedUrl = generateSecureMediaUrl('system/venusnap-d5340-b585dc46e9c1.json');
            $jsonContent = file_get_contents($signedUrl);

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
            // $title = $this->getNotificationTitle($notification->action);
            // $body = $this->getNotificationBody($notification);
            $title = $this->getNotificationTitle($notification);
            $body = $this->getNotificationBody($notification);
            $notificationData = $this->preparePushData($notification, $deliveryId);

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

                    NotificationDelivery::where('id', $deliveryId)->update([
                        'device_token' => $token,
                        'status' => 'sent'
                    ]);
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
            NotificationDelivery::where('id', $deliveryId)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        } finally {
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

    protected function sendBigPicturePushNotification(User $user, $notification, $deliveryId)
    {
        $activeTokens = FcmToken::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('token')
            ->toArray();

        if (empty($activeTokens)) {
            NotificationDelivery::where('id', $deliveryId)->update(['status' => 'failed']);
            return;
        }

        try {
            // Get Firebase credentials
            $signedUrl = generateSecureMediaUrl('system/venusnap-d5340-b585dc46e9c1.json');
            $jsonContent = file_get_contents($signedUrl);

            if ($jsonContent === false) {
                throw new \Exception('Failed to fetch Firebase credentials');
            }

            // Create temporary credentials file
            $tempFilePath = tempnam(sys_get_temp_dir(), 'firebase_cred_');
            file_put_contents($tempFilePath, $jsonContent);

            // Initialize Firebase
            $factory = (new Factory)->withServiceAccount($tempFilePath);
            $messaging = $factory->createMessaging();

            $albumName = $this->album->name;
            $albumDisplayName = str_contains(strtolower($albumName), 'album') ? $albumName : "{$albumName} Album";

            // Prepare notification content
            $titles = [
                "New Post Alert",
                "Fresh Drop in {$albumDisplayName}",
                "Something New Awaits",
                "{$albumDisplayName} Just Got Updated!"
            ];

            $title = $titles[array_rand($titles)];
            $bodies = [
                "New content just dropped in {$albumDisplayName}!",
                "Check out the latest update in {$albumDisplayName}",
                "Fresh content is now available in {$albumDisplayName}!",
                "Something new was added to {$albumDisplayName}!"
            ];

            $body = $bodies[array_rand($bodies)];
            $imageUrl = generateSecureMediaUrl($this->randomMedia->file_path_compress);
            $albumimageUrl = null;

            if ($this->album) {
                if ($this->album->type == 'personal' || $this->album->type == 'creator') {
                    $albumimageUrl = $this->album->thumbnail_compressed
                        ? generateSecureMediaUrl($this->album->thumbnail_compressed)
                        : ($this->album->thumbnail_original
                            ? generateSecureMediaUrl($this->album->thumbnail_original)
                            : $albumimageUrl);
                } elseif ($this->album->type == 'business') {
                    $albumimageUrl = $this->album->business_logo_compressed
                        ? generateSecureMediaUrl($this->album->business_logo_compressed)
                        : ($this->album->business_logo_original
                            ? generateSecureMediaUrl($this->album->business_logo_original)
                            : $albumimageUrl);
                }
            }

            // Build the message
            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create($title, $body))
                ->withData([
                    'type' => 'album_new_post',
                    'action' => 'album_new_post',
                    'post_id' => (string)$this->post->id,
                    'media_id' => (string)$this->randomMedia->id,
                    'album_id' => (string)$this->album->id,
                    'is_big_picture' => 'true',
                    'image' => $imageUrl,
                    'thumbnail' => $albumimageUrl,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'screen_to_open' => 'post',
                    'message_id' => (string)$deliveryId,
                ]);

            // Send to each active token for this user
            foreach ($activeTokens as $token) {
                try {
                    $messaging->send($message->withChangedTarget('token', $token));
                     NotificationDelivery::where('id', $deliveryId)->update([
                        'device_token' => $token,
                        'status' => 'sent'
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send to token {$token}: " . $e->getMessage());
                    FcmToken::where('token', $token)->update(['status' => 'expired']);
                    NotificationDelivery::where('id', $deliveryId)->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage()
                    ]);

                    SystemError::create([
                        'user_id' => $user->id,
                        'context' => 'fcm_send',
                        'message' => "Failed to send to token {$token}: " . $e->getMessage(),
                        'stack_trace' => $e->getTraceAsString(),
                        'metadata' => json_encode(['token' => $token]),
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Push notification failed for user ' . $user->id . ': ' . $e->getMessage(), [
                'post_id' => $this->post->id
            ]);
            SystemError::create([
                'user_id' => $user->id,
                'context' => 'push_notification',
                'message' => 'Push notification failed: ' . $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'metadata' => json_encode([
                    'user_id' => $user->id,
                    'post_id' => $this->post->id,
                ]),
            ]);
        } finally {
            // Clean up temporary file
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }

   protected function preparePushData($notification, $deliveryId): array
    {
        $data = json_decode($notification->data, true);
        $type = $this->determineTypeFromAction($notification->action);

        $imageUrl = $this->getNotificationImageUrl($notification, $data);

        // Use album profile picture if commenting as album
        if (in_array($notification->action, ['commented', 'reacted', 'replied'])) {
            $commenterType = $data['commenter_type'] ?? 'user';
            if ($commenterType === 'album' && isset($data['comment_as_album_id'])) {
                $album = Album::find($data['comment_as_album_id']);
                if ($album) {
                    $imageUrl = $this->getProfileUrl($album);
                }
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
            'notifiable_id' => isset($data['post_id']) ? (string)$data['post_id'] : (string)$notification->notifiable_id,
            'notifiablemedia_id' => $data['media_id'] ?? '0',
            'screen_to_open' => $this->getTargetScreen($notification->action),
            'metadata' => $this->sanitizeData($data),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'image' => $imageUrl,
            'message_id' => (string)$deliveryId,
        ];
    }

    protected function getTargetScreen($action): string
    {
        return match($action) {
            'viewed_album' => 'album_analytics',
            'commented', 'replied', 'liked', 'admired', 'album_new_post' => 'post',
            'shared_album', 'invited' => 'album_requests',
            default => 'notifications'
        };
    }

    protected function getNotificationImageUrl($notification, $data)
    {
        // For comments/replies - use album profile if commenting as album
        if (in_array($notification->action, ['commented', 'reacted', 'replied'])) {
            $commenterType = $data['commenter_type'] ?? 'user';
            if ($commenterType === 'album' && isset($data['comment_as_album_id'])) {
                $album = Album::find($data['comment_as_album_id']);
                if ($album) {
                    return $this->getProfileUrl($album);
                }
            }
        }

        // For album-related notifications
        if (in_array($notification->action, ['viewed_album', 'shared_album', 'invited'])) {
            $album = $this->notifiable;
            if ($album instanceof Album) {
                return $this->getProfileUrl($album);
            }
        }

        // For user-related notifications
        $sender = User::find($data['sender_id'] ?? null);
        if ($sender) {
            return $sender->profile_compressed
                ? generateSecureMediaUrl($sender->profile_compressed)
                : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($sender->email))) . '?s=100&d=mp';
        }

        return null;
    }

    protected function getProfileUrl($album)
    {
        if ($album->type === 'personal' || $album->type === 'creator') {
            return $album->thumbnail_compressed
                ? generateSecureMediaUrl($album->thumbnail_compressed)
                : ($album->thumbnail_original
                    ? generateSecureMediaUrl($album->thumbnail_original)
                    : 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y');
        } elseif ($album->type === 'business') {
            return $album->business_logo_compressed
                ? generateSecureMediaUrl($album->business_logo_compressed)
                : ($album->business_logo_original
                    ? generateSecureMediaUrl($album->business_logo_original)
                    : 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y');
        }

        return 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';
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
            'invited' => 'album_request',
            'album_new_post' => 'post',
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

        // Get commenter information
        $commenterType = $data['commenter_type'] ?? 'user';
        $commenterName = $data['username'] ?? 'Someone';
        $albumName = $data['album_name'] ?? null;
        $commentType = $data['comment_type'] ?? 'text';

        switch ($action) {
            case 'viewed_album':
                $albumName = $data['album_name'] ?? 'your album';
                return "explored your $albumName Album";

            case 'commented':
                if ($commenterType === 'album') {
                    $albumName = $data['comment_as_album_name'] ?? $commenterName;
                    return $commentType === 'gif' ?
                        "{$albumName} reacted with a GIF to your post" :
                        "{$albumName} commented on your post";
                } else {
                    return $commentType === 'gif' ?
                        "{$commenterName} reacted with a GIF to your post" :
                        "{$commenterName} commented on your post";
                }

            case 'reacted': // New case for GIF reactions
                if ($commenterType === 'album') {
                    $albumName = $data['comment_as_album_name'] ?? $commenterName;
                    return "{$albumName} reacted to your post";
                } else {
                    return "{$commenterName} reacted to your post";
                }

            case 'replied':
                $isAlbumOwner = $data['is_album_owner'] ?? false;
                $replierType = $data['replier_type'] ?? 'user';
                $replierName = $data['username'] ?? 'Someone';

                if ($replierType === 'album') {
                    $albumName = $data['reply_as_album_name'] ?? $replierName;
                    return "{$albumName} replied to your comment";
                } else {
                    return $isAlbumOwner ?
                        "{$albumName} replied to your comment" :
                        "{$replierName} replied to your comment";
                }

            case 'liked':
                $likerType = $data['liker_type'] ?? 'user';
                $likerName = $data['username'] ?? 'Someone';

                if ($likerType === 'album') {
                    $albumName = $data['like_as_album_name'] ?? $likerName;
                    return "{$albumName} liked your post";
                } else {
                    return "{$likerName} liked your post";
                }

            case 'admired':
                $admirerType = $data['admirer_type'] ?? 'user';
                $admirerName = $data['username'] ?? 'Someone';

                if ($admirerType === 'album') {
                    $albumName = $data['admire_as_album_name'] ?? $admirerName;
                    return "{$albumName} admired your snap";
                } else {
                    $albumName = $data['album_name'] ?? null;
                    return $albumName ?
                        "{$admirerName} admired your snap in $albumName Album" :
                        "{$admirerName} admired your snap";
                }

            case 'shared_album':
            case 'invited':
                $albumName = $data['album_name'] ?? 'an album';
                return "invited you to collaborate on $albumName Album";

            default:
                return "sent you a notification";
        }
    }
}
