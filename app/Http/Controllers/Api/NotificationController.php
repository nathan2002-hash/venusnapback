<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\PostMedia;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    $notifications = Notification::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy(function ($notification) {
            $type = $notification->type ?? $this->determineTypeFromAction($notification->action);
            $groupKey = $type . '-' . $this->getGroupingIdentifier($notification, $type);

            $data = json_decode($notification->data, true);
            if (isset($data['username'])) {
                $groupKey .= '-' . $data['username'];
            }

            return $groupKey;
        })
        ->map(function ($group) {
            $firstNotification = $group->first();
            $data = json_decode($firstNotification->data, true);
            $username = $data['username'] ?? 'Someone';

            $action = $firstNotification->action;
            $type = $firstNotification->type ?? $this->determineTypeFromAction($action);
            $notifiableId = $this->getProperNotifiableId($firstNotification, $type);

            if ($type === 'album_request') {
                $message = "$username invited you to collaborate on the album \"{$data['album_name']}\"";
            } else {
                $userCount = $group->count();
                $message = $this->buildGroupedMessage([$username], $userCount, $action, $type, $firstNotification);
            }

            return [
                'id' => $firstNotification->id,
                'type' => $type,
                'action' => $action,
                'notifiable_id' => $notifiableId,
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

    // protected function getGroupingIdentifier($notification, $type)
    // {
    //     // For posts, we want to group by the post_id (from post_media) not the post_media_id
    //     if ($type === 'post') {
    //         try {
    //             $postMedia = PostMedia::find($notification->notifiable_id);
    //             return $postMedia ? $postMedia->post_id : $notification->notifiable_id;
    //         } catch (\Exception $e) {
    //             return $notification->notifiable_id;
    //         }
    //     }

    //     return $notification->notifiable_id;
    // }

    protected function getGroupingIdentifier($notification, $type)
    {
        if ($type === 'post') {
            try {
                $postMedia = PostMedia::find($notification->notifiable_id);
                return $postMedia ? $postMedia->post_id : $notification->notifiable_id;
            } catch (\Exception $e) {
                return $notification->notifiable_id;
            }
        }

        if ($type === 'album_view') {
            // Group by album and date (e.g., 2025-04-11)
            $data = json_decode($notification->data, true);
            $albumId = $data['album_id'] ?? $notification->notifiable_id;
            $date = $notification->created_at->format('Y-m-d');
            return $albumId . '-' . $date;
        }

        return $notification->notifiable_id;
    }


    protected function getProperNotifiableId($notification, $type)
    {
        // Only modify notifiable_id for post types
        if ($type === 'post') {
            try {
                $postMedia = PostMedia::find($notification->notifiable_id);
                return $postMedia ? $postMedia->post_id : $notification->notifiable_id;
            } catch (\Exception $e) {
                return $notification->notifiable_id;
            }
        }

        // For all other types, return the original notifiable_id
        return $notification->notifiable_id;
    }

    // private function determineTypeFromAction($action)
    // {
    //     $typeMap = [
    //         'invited' => 'album_request',
    //         'admired' => 'post',
    //         'liked' => 'post',
    //         'commented' => 'comment',
    //         'replied' => 'comment',
    //         //'replied' => 'comment'
    //     ];

    //     return $typeMap[$action] ?? 'post';
    // }

    private function determineTypeFromAction($action)
    {
        $typeMap = [
            'invited' => 'album_request',
            'admired' => 'post',
            'liked' => 'post',
            'commented' => 'comment',
            'replied' => 'comment',
            'viewed_album' => 'album_view', // ✅ New type
        ];

        return $typeMap[$action] ?? 'post';
    }


    // private function buildGroupedMessage($usernames, $userCount, $action, $type, $notification)
    // {
    //     $actionPhrase = $this->getActionPhrase($action, $type, $notification);

    //     if (empty($usernames)) {
    //         return "Someone $actionPhrase";
    //     }

    //     if ($userCount == 1) return "{$usernames[0]} $actionPhrase";
    //     if ($userCount == 2) return "{$usernames[0]} and {$usernames[1]} $actionPhrase";
    //     if ($userCount == 3) return "{$usernames[0]}, {$usernames[1]}, and {$usernames[2]} $actionPhrase";

    //     return "{$usernames[0]} and " . ($userCount - 1) . " others $actionPhrase";
    // }

    private function buildGroupedMessage($usernames, $userCount, $action, $type, $notification)
    {
        if ($type === 'album_view') {
            $data = json_decode($notification->data, true);
            $album = \App\Models\Album::find($data['album_id'] ?? null);
            $albumName = $album ? $album->name : 'your album';
            return "$userCount people explored \"$albumName\" today";
        }

        $actionPhrase = $this->getActionPhrase($action, $type, $notification);

        if (empty($usernames)) {
            return "Someone $actionPhrase";
        }

        if ($userCount == 1) return "{$usernames[0]} $actionPhrase";
        if ($userCount == 2) return "{$usernames[0]} and {$usernames[1]} $actionPhrase";
        if ($userCount == 3) return "{$usernames[0]}, {$usernames[1]}, and {$usernames[2]} $actionPhrase";

        return "{$usernames[0]} and " . ($userCount - 1) . " others $actionPhrase";
    }




    private function getActionPhrase($action, $type, $notification)
{
    $data = json_decode($notification->data, true);

    $albumName = null;
    if (isset($data['album_id'])) {
        $album = \App\Models\Album::find($data['album_id']);
        if ($album) {
            $albumName = $album->name;
        }
    }

    $phrases = [
        'comment' => [
            'commented' => "commented on your snap on {$albumName} Album",
            'replied' => 'replied to your comment',
        ],
        'post' => [
            'liked' => 'liked your post',
            'admired' => $albumName ? "admired your snap on {$albumName} Album" : 'admired your snap',
            'shared' => 'shared your post',
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
            'invited' => 'group_add'
        ];

        return $icons[$action] ?? 'notifications';
    }


    private function getActionhrase($action, $type, $notification)
    {
        $data = json_decode($notification->data, true);

        $phrases = [
            'comment' => [
                'commented' => 'commented on your snap',
                'replied' => 'replied to your comment',
            ],
            'post' => [
                'liked' => 'liked your post',
                'admired' => isset($data['album_name']) ? "admired your snap on \"{$data['album_name']}\"" : 'admired your snap',
                'shared' => 'shared your post',
            ],
            'album_request' => [
                'shared_album' => 'invited you to collaborate on an album',
                'invited' => 'invited you to collaborate on an album',
            ],
        ];

        return $phrases[$type][$action] ?? $action;
    }
    private function getNotificationTitle($action)
    {
        switch ($action) {
            case 'shared_album':
                return 'Album Invite Request';
            case 'admired':
                return 'Admire';
            case 'support':
                return 'Support';
            case 'commented':
                return 'Comment';
            default:
                return ucfirst(str_replace('_', ' ', $action));
        }
    }

    private function getNotificationDescription($notification)
    {
        $data = json_decode($notification->data, true);
        $username = $data['username'] ?? 'Someone';

        switch ($notification->action) {
            case 'shared_album':
                return "$username invited you to edit the album \"{$data['album_name']}\"";
            case 'admired':
                return "$username admired your post";
            case 'liked':
                return "$username liked your post";
            case 'commented':
                return "$username commented on your snap";
            default:
                return "$username performed an action";
        }
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

        // Fetch the count of unread notifications for the user
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $unreadCount]);
    }
}
