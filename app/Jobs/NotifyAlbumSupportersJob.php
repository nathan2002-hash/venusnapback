<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Post;
use App\Models\User;
use App\Models\Album;
use App\Models\FcmToken;
use App\Models\UserSetting;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Models\Notification as NotificationModel;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotifyAlbumSupportersJob implements ShouldQueue
{
    use Queueable;

    protected $post;
    protected $album;
    protected $randomMedia;
    /**
     * Create a new job instance.
     */
    public function __construct(Post $post, Album $album, $randomMedia)
    {
        $this->post = $post;
        $this->album = $album;
        $this->randomMedia = $randomMedia;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Get all supporters of this album with push notifications enabled
           $supporters = $this->album->supporters()->with('user')->get();

            foreach ($supporters as $supporter) {
                $user = $supporter->user;

                if (!$user) {
                    continue; // Just in case the user was deleted or missing
                }

                $settings = UserSetting::where('user_id', $user->id)->first();

                if (!$settings || !$settings->push_notifications) {
                    continue;
                }

                $this->createAndSendNotification($user);
            }

        } catch (\Exception $e) {
            Log::error('Error notifying album supporters: ' . $e->getMessage(), [
                'post_id' => $this->post->id,
                'album_id' => $this->album->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    protected function createAndSendNotification(User $supporter)
    {
        try {
            // Create database notification
            $notification = NotificationModel::create([
                'user_id' => $supporter->id,
                'action' => 'album_new_post',
                'notifiable_type' => get_class($this->post),
                'notifiable_id' => $this->post->id,
                'data' => json_encode([
                    'username' => $this->post->user->name,
                    'sender_id' => $this->post->user_id,
                    'album_name' => $this->album->name,
                    'post_id' => $this->post->id,
                    'media_id' => $this->randomMedia->id,
                    'image' => Storage::disk('s3')->url($this->randomMedia->file_path)
                ]),
                'is_read' => false
            ]);

            // Send push notification
            $this->sendPushNotification($supporter);
        } catch (\Exception $e) {
            Log::error('Error creating notification for supporter: ' . $e->getMessage(), [
                'supporter_id' => $supporter->id,
                'post_id' => $this->post->id
            ]);
        }
    }

    protected function sendPushNotification(User $user)
    {
        $activeTokens = FcmToken::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('token')
            ->toArray();

        if (empty($activeTokens)) {
            return;
        }

        try {
            // Get Firebase credentials
            $jsonContent = file_get_contents('https://cdn.venusnap.com/system/venusnap-d5340-firebase-adminsdk-fbsvc-b55072fb51.json');

            if ($jsonContent === false) {
                throw new \Exception('Failed to fetch Firebase credentials');
            }

            // Create temporary credentials file
            $tempFilePath = tempnam(sys_get_temp_dir(), 'firebase_cred_');
            file_put_contents($tempFilePath, $jsonContent);

            // Initialize Firebase
            $factory = (new Factory)->withServiceAccount($tempFilePath);
            $messaging = $factory->createMessaging();

            // Prepare notification content
            $title = "New post in {$this->album->name}";
            $body = "{$this->post->user->name} posted new content";
            $imageUrl = Storage::disk('s3')->url($this->randomMedia->file_path);
            $albumimageUrl = null;

            if ($this->album) {
                if ($this->album->type == 'personal' || $this->album->type == 'creator') {
                    $albumimageUrl = $this->album->thumbnail_compressed
                        ? Storage::disk('s3')->url($this->album->thumbnail_compressed)
                        : ($this->album->thumbnail_original
                            ? Storage::disk('s3')->url($this->album->thumbnail_original)
                            : $albumimageUrl);
                } elseif ($this->album->type == 'business') {
                    $albumimageUrl = $this->album->business_logo_compressed
                        ? Storage::disk('s3')->url($this->album->business_logo_compressed)
                        : ($this->album->business_logo_original
                            ? Storage::disk('s3')->url($this->album->business_logo_original)
                            : $albumimageUrl);
                }
            }

            // Build the message
            $message = CloudMessage::new()
                ->withNotification(FirebaseNotification::create($title, $body))
                ->withData([
                    'type' => 'album_post',
                    'action' => 'album_new_post',
                    'post_id' => (string)$this->post->id,
                    'media_id' => (string)$this->randomMedia->id,
                    'album_id' => (string)$this->album->id,
                    'is_big_picture' => 'true',
                    'image' => $imageUrl,
                    'thumbnail' => $albumimageUrl,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'screen_to_open' => 'post'
                ]);

            // Send to each active token
            foreach ($activeTokens as $token) {
                try {
                    $messaging->send($message->withChangedTarget('token', $token));
                } catch (\Exception $e) {
                    Log::error("Failed to send to token {$token}: " . $e->getMessage());
                    FcmToken::where('token', $token)->update(['status' => 'expired']);
                }
            }

        } catch (\Exception $e) {
            Log::error('Push notification failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'post_id' => $this->post->id
            ]);
        } finally {
            // Clean up temporary file
            if (isset($tempFilePath) && file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}
