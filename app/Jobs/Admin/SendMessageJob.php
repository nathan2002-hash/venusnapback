<?php

namespace App\Jobs\Admin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Models\Notification as NotificationModel;
use App\Models\FcmToken;
use App\Models\UserSetting;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Queueable;
    protected $targetUserId;
    protected $title;
    protected $body;
    protected $messageData;
    protected $isImportant;


    /**
     * Create a new job instance.
     */
    public function __construct($targetUserId, $title, $body, $messageData = [], $isImportant = false)
    {
        $this->targetUserId = $targetUserId;
        $this->title = $title;
        $this->body = $body;
        $this->messageData = $messageData;
        $this->isImportant = $isImportant;
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
                'action' => 'message_center',
                'notifiable_type' => 'App\Models\Notice', // Custom type for message center
                'notifiable_id' => 0, // No specific notifiable object
                'data' => $this->prepareNotificationDataForStorage(),
                'group_count' => 0,
                'is_read' => false,
            ];

            $notification = NotificationModel::create($notificationData);

            // Now send the push notification
            $this->sendPushNotification($notification);

        } catch (\Exception $e) {
            Log::error('Message Center Notification creation failed: ' . $e->getMessage(), [
                'target_user' => $this->targetUserId,
                'title' => $this->title,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    protected function prepareNotificationDataForStorage(): string
    {
        $baseData = [
            'title' => $this->title,
            'message' => $this->body,
            'is_important' => $this->isImportant,
        ];

        // Merge with additional message data
        $mergedData = array_merge($baseData, $this->messageData);

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
            $title = $this->title;
            $body = $this->body;
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
            Log::error('Message Center Push notification failed: ' . $e->getMessage(), [
                'user_id' => $this->targetUserId,
                'title' => $this->title,
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

        return [
            'type' => 'message_center',
            'action' => 'message_center',
            'notifiable_id' => '0',
            'notifiablemedia_id' => '0',
            'screen_to_open' => 'message_center',
            'metadata' => $this->sanitizeData($data),
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            'image' => null, // Message center notifications typically don't have images
            'is_important' => $this->isImportant ? 'true' : 'false',
        ];
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

}
