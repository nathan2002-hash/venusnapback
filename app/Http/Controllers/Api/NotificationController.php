<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\FcmToken;
use App\Models\PostMedia;
use App\Models\UserSetting;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
     public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->groupBy(function ($notification) {
                $type = $notification->type ?? $this->determineTypeFromAction($notification->action);

                // Special handling for album views
                if ($type === 'album_view') {
                    $data = json_decode($notification->data, true);
                    $albumId = $data['album_id'] ?? $notification->notifiable_id;
                    $date = $notification->created_at->format('Y-m-d');
                    return "album_view-{$albumId}-{$date}";
                }

                // Default grouping for other types
                $groupKey = $type . '-' . $this->getGroupingIdentifier($notification, $type);

                if ($type !== 'album_view') {
                    $data = json_decode($notification->data, true);
                    if (isset($data['username'])) {
                        $groupKey .= '-' . $data['username'];
                    }
                }

                return $groupKey;
            })
            ->map(function ($group) {
                $firstNotification = $group->first();
                $data = json_decode($firstNotification->data, true);
                $username = $data['username'] ?? 'Someone';

                $action = $firstNotification->action;
                $type = $firstNotification->type ?? $this->determineTypeFromAction($action);

                // Get both notifiable_id and notifiablemedia_id
                $ids = $this->getNotificationIds($firstNotification, $type);
                $notifiableId = $ids['notifiable_id'];
                $notifiableMediaId = $ids['notifiablemedia_id'];

                $userCount = $group->count();

                if ($type === 'album_request') {
                    $message = "$username invited you to collaborate on the album \"{$data['album_name']}\"";
                } else {
                    $message = $this->buildGroupedMessage([$username], $userCount, $action, $type, $firstNotification, $group);
                }

                return [
                    'id' => $firstNotification->id,
                    'type' => $type,
                    'action' => $action,
                    'notifiable_id' => $notifiableId,
                    'notifiablemedia_id' => $notifiableMediaId,
                    'original_notifiable_id' => $firstNotification->notifiable_id,
                    'message' => $message,
                    'is_read' => $group->every->is_read,
                    'created_at' => $firstNotification->created_at,
                    'formatted_date' => $firstNotification->created_at->format('M d, Y - h:i A'),
                    'icon' => $this->getNotificationIcon($action),
                    'metadata' => $data
                ];
            })
            ->values();

        return response()->json($notifications);
    }


   protected function getGroupingIdentifier($notification, $type)
    {
        if ($type === 'post' || $type === 'comment') {
            try {
                $postMedia = PostMedia::find($notification->notifiable_id);
                return $postMedia ? $postMedia->post_id : $notification->notifiable_id;
            } catch (\Exception $e) {
                return $notification->notifiable_id;
            }
        }

        if ($type === 'album_view' || $type === 'album_request') {
            $data = json_decode($notification->data, true);
            $albumId = $data['album_id'] ?? $notification->notifiable_id;

            // For album_view, group by date as well
            if ($type === 'album_view') {
                $date = $notification->created_at->format('Y-m-d');
                return $albumId . '-' . $date;
            }

            return $albumId;
        }

        return $notification->notifiable_id;
    }


    protected function getNotificationIds($notification, $type)
    {
        $result = [
            'notifiable_id' => $notification->notifiable_id,
            'notifiablemedia_id' => null
        ];

        // For post and comment types, we need to get both the post_id and media_id
         if (in_array($type, ['post', 'comment', 'album_new_post'])) {
            try {
                $postMedia = PostMedia::find($notification->notifiable_id);
                if ($postMedia) {
                    $result['notifiable_id'] = $postMedia->post_id;
                    $result['notifiablemedia_id'] = $postMedia->id;
                }
            } catch (\Exception $e) {
                // Keep original values if error occurs
            }
        }
        // For album access, ensure notifiable_id contains the album_id
        elseif ($type === 'album_view' || $type === 'album_request') {
            $data = json_decode($notification->data, true);
            if (isset($data['album_id'])) {
                $result['notifiable_id'] = $data['album_id'];
            }
        }

        return $result;
    }

    private function determineTypeFromAction($action)
    {
        $typeMap = [
            'invited' => 'album_request',
            'admired' => 'post',
            'liked' => 'post',
            'commented' => 'comment',
            'replied' => 'comment',
            'viewed_album' => 'album_view',
            'album_new_post' => 'album_new_post',
        ];

        return $typeMap[$action] ?? 'post';
    }

    private function buildGroupedMessage($usernames, $userCount, $action, $type, $notification, $group)
    {
        if ($type === 'album_view') {
            $data = json_decode($notification->data, true);
            $album = \App\Models\Album::with('user')->find($data['album_id'] ?? null);
            $albumName = $album ? $album->name : 'your album';
            $ownerUsername = $album?->user?->name;

            $notificationDate = $notification->created_at->timezone(config('app.timezone'))->startOfDay();
            $today = now()->timezone(config('app.timezone'))->startOfDay();
            $diffInDays = $notificationDate->diffInDays($today);

            $timePhrase = match (true) {
                $diffInDays === 0 => 'today',
                $diffInDays === 1 => 'yesterday',
                $diffInDays <= 6 => 'on ' . $notificationDate->format('l'),
                default => 'on ' . $notificationDate->format('M j'),
            };

            // Get all unique usernames from the group, excluding the owner
            $allUsernames = $group->map(function ($n) use ($ownerUsername) {
                $data = json_decode($n->data, true);
                $username = $data['username'] ?? null;
                return $username && $username !== $ownerUsername ? $username : null;
            })->filter()->unique()->values()->toArray();

            $filteredCount = count($allUsernames);

            if (!empty($allUsernames)) {
                if ($filteredCount === 1) {
                    return "{$allUsernames[0]} explored your album \"$albumName\" $timePhrase";
                }
                if ($filteredCount === 2) {
                    return "{$allUsernames[0]} and {$allUsernames[1]} explored your album \"$albumName\" $timePhrase";
                }
                if ($filteredCount === 3) {
                    return "{$allUsernames[0]}, {$allUsernames[1]}, and {$allUsernames[2]} explored your album \"$albumName\" $timePhrase";
                }
                return "{$allUsernames[0]} and " . ($filteredCount - 1) . " others explored your album \"$albumName\" $timePhrase";
            }

            // If all viewers were the owner
            return null;
        }

        // Fallback for other types
        $actionPhrase = $this->getActionPhrase($action, $type, $notification);

        // Filter out empty usernames and ensure we have unique values
        $usernames = array_filter(array_unique($usernames));
        $userCount = count($usernames);

        if (empty($usernames)) {
            return "Someone $actionPhrase";
        }

        // Get the first few usernames (up to 3)
        $displayUsernames = array_slice($usernames, 0, 3);

        if ($userCount == 1) {
            return "{$displayUsernames[0]} $actionPhrase";
        }
        if ($userCount == 2) {
            return "{$displayUsernames[0]} and {$displayUsernames[1]} $actionPhrase";
        }
        if ($userCount == 3) {
            return "{$displayUsernames[0]}, {$displayUsernames[1]}, and {$displayUsernames[2]} $actionPhrase";
        }

        return "{$displayUsernames[0]} and " . ($userCount - 1) . " others $actionPhrase";
    }

    private function getActionPhrase($action, $type, $notification)
    {
        $data = json_decode($notification->data, true);
        $isAlbumOwner = $data['is_album_owner'] ?? false;
        $albumName = $data['album_name'] ?? null;

        $albumDisplayName = str_contains(strtolower($albumName), 'album') ? $albumName : "{$albumName} Album";

        $albumNewPostPhrases = [
            "added a new snap",
            "shared something new",
            "just got updated",
            "posted a fresh snap",
        ];


        $phrases = [
            'comment' => [
                'commented' => "commented on your snap" . ($albumName ? " in {$albumName}" : ""),
                'replied' => $isAlbumOwner ? "replied to your comment in {$albumName}" : "replied to your comment",
            ],
            'post' => [
                'liked' => 'liked your post',
                'admired' => $albumName ? "admired your snap in {$albumName}" : 'admired your snap',
                'shared' => 'shared your post',
            ],
            'album_new_post' => [
                'album_new_post' => $albumNewPostPhrases[array_rand($albumNewPostPhrases)],
            ],
            'album_request' => [
                'shared_album' => 'invited you to collaborate on an album',
                'invited' => 'invited you to collaborate on an album',
            ],
        ];

        return $phrases[$type][$action] ?? $action;
    }

    private function getNotificationIcon($action)
    {
        $icons = [
            'liked' => 'thumb_up',
            'admired' => 'favorite',
            'commented' => 'comment',
            'replied' => 'reply',
            'shared' => 'share',
            'shared_album' => 'album',
            'viewed_album' => 'album',
            'invited' => 'group_add',
            'album_new_post' => 'photo_library',
        ];

        return $icons[$action] ?? 'notifications';
    }

    public function storeFcmToken(Request $request)
    {
        $user = Auth::user();
        $token = $request->fcm_token;
        $userAgent = $request->header('User-Agent');
        $deviceInfo = $request->header('Device-Info') ?? $userAgent;

        // Step 1: Check if the token already exists for this user and device_info and is active
        $alreadyExists = FcmToken::where('user_id', $user->id)
            ->where('device_info', $deviceInfo)
            ->where('token', $token)
            ->where('status', 'active')
            ->first();

        if ($alreadyExists) {
            // No need to do anything
            return response()->json(['status' => 'unchanged']);
        }

        // Step 2: Expire this token if used by another user
        FcmToken::where('token', $token)
            ->where('user_id', '!=', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Step 3: Expire any existing active tokens for this device and user (not this token)
        FcmToken::where('user_id', $user->id)
            ->where('device_info', $deviceInfo)
            ->where('token', '!=', $token) // <-- only expire different token
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        // Step 4: Create or reactivate the token if needed
        $existing = FcmToken::where('user_id', $user->id)
            ->where('device_info', $deviceInfo)
            ->where('token', $token)
            ->first();

        if ($existing) {
            // Token exists but was expired, just reactivate it
            $existing->status = 'active';
            $existing->save();
        } else {
            // New token for this device
            FcmToken::create([
                'user_id' => $user->id,
                'token' => $token,
                'device_info' => $deviceInfo,
                'user_agent' => $userAgent,
                'status' => 'active',
            ]);
        }

        return response()->json(['status' => 'stored']);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer',
        ]);

        $user = $request->user();

        // Get the notification being marked as read
        $notification = Notification::where('id', $request->notification_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }

        // Determine the type (fallback to auto-detection if not set)
        $type = $notification->type ?? $this->determineTypeFromAction($notification->action);

        // Get all notifications in the same group
        $groupedNotifications = Notification::where('user_id', $user->id)
            ->where(function($query) use ($notification, $type) {
                // For posts, we need to handle the post_media -> post relationship
                if ($type === 'post') {
                    $postMedia = PostMedia::find($notification->notifiable_id);
                    if ($postMedia) {
                        // Find all notifications for post_media items belonging to this post
                        $postMediaIds = PostMedia::where('post_id', $postMedia->post_id)
                            ->pluck('id');
                        $query->whereIn('notifiable_id', $postMediaIds);
                    } else {
                        $query->where('notifiable_id', $notification->notifiable_id);
                    }
                } else {
                    $query->where('notifiable_id', $notification->notifiable_id);
                }

                // Include the same action and type
                $query->where('action', $notification->action);
                if ($notification->type) {
                    $query->where('type', $notification->type);
                }
            })
            ->get();

        // Mark all grouped notifications as read
        $updated = Notification::whereIn('id', $groupedNotifications->pluck('id'))
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notifications marked as read',
            'count' => $updated,
        ]);
    }

    public function notificationscount()
    {
        $user = Auth::user();

        // Get all notifications grouped the same way as the index method
        $groupedNotifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($notification) {
                $type = $notification->type ?? $this->determineTypeFromAction($notification->action);

                if ($type === 'album_view') {
                    $data = json_decode($notification->data, true);
                    $albumId = $data['album_id'] ?? $notification->notifiable_id;
                    $date = $notification->created_at->format('Y-m-d');
                    return "album_view-{$albumId}-{$date}";
                }

                return $type . '-' . $this->getGroupingIdentifier($notification, $type);
            });

        // Count only unread notification GROUPS (not individual notifications)
        $unreadCount = $groupedNotifications->filter(function ($group) {
            return $group->contains('is_read', false);
        })->count();

        return response()->json(['count' => $unreadCount]);
    }

    public function markAllUserNotificationsAsRead(Request $request)
    {
        $user = $request->user();

        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

}
