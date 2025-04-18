<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
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
            // Determine type from action if not set
            $type = $notification->type ?? $this->determineTypeFromAction($notification->action);
            return $type . '-' . $notification->notifiable_id;
        })
        ->map(function ($group) {
            $firstNotification = $group->first();
            $userCount = $group->count();
            
            $usernames = $group->take(3)->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return $data['username'] ?? 'Someone';
            })->filter()->values()->toArray();
            
            $action = $firstNotification->action;
            $type = $firstNotification->type ?? $this->determineTypeFromAction($action);
            
            return [
                'id' => $firstNotification->id,
                'type' => $type,
                'action' => $action,
                'notifiable_id' => $firstNotification->notifiable_id,
                'message' => $this->buildGroupedMessage($usernames, $userCount, $action, $type),
                'is_read' => $group->every->is_read,
                'created_at' => $firstNotification->created_at,
                'formatted_date' => $firstNotification->created_at->format('M d, Y - h:i A'),
                'icon' => $this->getNotificationIcon($action),
                'metadata' => json_decode($firstNotification->data, true)
            ];
        })
        ->values();

    return response()->json($notifications);
}

    private function determineTypeFromAction($action)
{
    $typeMap = [
        'shared_album' => 'album_request',
        'admired' => 'post',
        'liked' => 'post',
        'commented' => 'comment'
    ];
    
    return $typeMap[$action] ?? 'post';
}

   private function buildGroupedMessage($usernames, $userCount, $action, $type)
{
    $actionPhrase = $this->getActionPhrase($action, $type);
    
    if (empty($usernames)) {
        return "Someone $actionPhrase";
    }
    
    if ($userCount == 1) return "{$usernames[0]} $actionPhrase";
    if ($userCount == 2) return "{$usernames[0]} and {$usernames[1]} $actionPhrase";
    if ($userCount == 3) return "{$usernames[0]}, {$usernames[1]}, and {$usernames[2]} $actionPhrase";
    
    return "{$usernames[0]} and " . ($userCount - 1) . " others $actionPhrase";
}


   private function getActionPhrase($action, $type)
{
    $phrases = [
        'comment' => [
            'created' => 'commented on your post',
            'replied' => 'replied to your comment'
        ],
        'post' => [
            'liked' => 'liked your post',
            'admired' => 'admired your post',
            'shared' => 'shared your post'
        ],
        'album_request' => [
            'shared_album' => 'shared an album with you',
            'invited' => 'invited you to collaborate on an album'
        ]
    ];
    
    return $phrases[$type][$action] ?? $action;
}


    private function getNotificationIcon($action)
{
    $icons = [
        'liked' => 'thumb_up',
        'admired' => 'favorite',
        'commented' => 'comment',
        'shared' => 'share',
        'shared_album' => 'album',
        'invited' => 'group_add'
    ];
    
    return $icons[$action] ?? 'notifications';
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
                return "$username commented on your post";
            default:
                return "$username performed an action";
        }
    }



    public function markAsRead(Request $request)
    {
        $request->validate([
            'post_media_id' => 'required|integer',
            'action' => 'required|string', // Add action to the request validation
        ]);

        // Get the authenticated user
        $user = $request->user();

        // Mark all notifications as read for the given post_media_id, user_id, and action
        $updated = Notification::where('notifiable_id', $request->post_media_id)
            ->where('user_id', $user->id)
            ->where('action', $request->action) // Filter by action
            ->update(['is_read' => true]); // Update all matching notifications

        if ($updated > 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'Notifications marked as read',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No notifications found',
        ], 404);
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
