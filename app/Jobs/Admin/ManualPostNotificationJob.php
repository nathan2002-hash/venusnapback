<?php

namespace App\Jobs\Admin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Post;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\UserSetting;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\Notification as NotificationModel;

class ManualPostNotificationJob implements ShouldQueue
{
    use Queueable;
    protected $post;
    protected $recipient;
    protected $title;
    protected $message;
    protected $isImportant;
    protected $sendPush;

    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, User $recipient, $title, $message, $isImportant = false, $sendPush = true)
    {
        $this->post = $post;
        $this->recipient = $recipient;
        $this->title = $title;
        $this->message = $message;
        $this->isImportant = $isImportant;
        $this->sendPush = $sendPush;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Check if user wants to receive notifications
            $settings = UserSetting::where('user_id', $this->recipient->id)->first();
            if (!$settings || !$settings->push_notifications) {
                return;
            }

            // Create notification record
            $notification = NotificationModel::create([
                'user_id' => $this->recipient->id,
                'action' => 'album_new_post',
                'notifiable_type' => get_class($this->post),
                'notifiable_id' => $this->post->id,
                'data' => json_encode([
                    'title' => $this->title,
                    'message' => $this->message,
                    'is_important' => $this->isImportant,
                    'post_id' => $this->post->id,
                    'sender_id' => $this->post->user_id,
                    'sender_name' => $this->post->user->name,
                ]),
                'is_read' => false
            ]);

            // Send push notification if enabled
            if ($this->sendPush) {
                $this->sendPushNotification($notification);
            }

        } catch (\Exception $e) {
            \Log::error('Manual post notification failed: ' . $e->getMessage(), [
                'post_id' => $this->post->id,
                'recipient_id' => $this->recipient->id
            ]);
        }
    }

    protected function sendPushNotification($notification)
    {
        // Get all active FCM tokens for the recipient
        $activeTokens = FcmToken::where('user_id', $this->recipient->id)
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

            // Get a media item from the post for the image
            $media = $this->post->media->first();
            $imageUrl = $media ? generateSecureMediaUrl($media->file_path_compress) : null;

            // Prepare notification data
            $notificationData = [
                'type' => 'album_new_post',
                'action' => 'album_new_post',
                'post_id' => (string)$this->post->id,
                'sender_id' => (string)$this->post->user_id,
                'is_important' => $this->isImportant ? 'true' : 'false',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'screen_to_open' => 'post',
                'image' => $imageUrl,
            ];

            // Send to each active token
            foreach ($activeTokens as $token) {
                try {
                    $message = CloudMessage::withTarget('token', $token)
                        ->withNotification(FirebaseNotification::create($this->title, $this->message))
                        ->withHighestPossiblePriority()
                        ->withData($notificationData);

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
                    \Log::error('Failed to send to token: ' . $token, [
                        'error' => $e->getMessage(),
                        'user_id' => $this->recipient->id
                    ]);

                    // Mark failed token as expired
                    FcmToken::where('token', $token)->update(['status' => 'expired']);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Push notification failed: ' . $e->getMessage(), [
                'user_id' => $this->recipient->id,
                'notification_id' => $notification->id
            ]);
        } finally {
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}
