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

        // Fetch all notifications for the user
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($notification) {
                // Group by action and notifiable_id (e.g., post ID)
                return $notification->action . '-' . $notification->notifiable_id;
            })
            ->map(function ($group) {
                // Get the first notification in the group for metadata
                $firstNotification = $group->first();

                // Count the number of users in the group
                $userCount = $group->count();

                // Get the usernames of the first 3 users
                $usernames = $group->take(3)->map(function ($notification) {
                    return json_decode($notification->data, true)['username'];
                });

                // Build the grouped notification
                return [
                    'id' => $firstNotification->id,
                    'user_id' => $firstNotification->user_id,
                    'action' => $firstNotification->action,
                    'notifiable_type' => $firstNotification->notifiable_type,
                    'notifiable_id' => $firstNotification->notifiable_id,
                    'data' => [
                        'usernames' => $usernames,
                        'user_count' => $userCount,
                        'description' => $this->getNotificationDescription($firstNotification), 
                    ],
                    'group_count' => $userCount,
                    'is_read' => $group->every(function ($notification) {
                        return $notification->is_read;
                    }),
                    'created_at' => $firstNotification->created_at,
                    'formatted_date' => Carbon::parse($firstNotification->created_at)->format('M d, Y - h:i A'),
                    'title' => $this->getNotificationTitle($firstNotification->action),
                    'description' => $this->getNotificationDescription($firstNotification), // 👈 this
                    'icon' => $this->getNotificationIcon($firstNotification->action),
                ];
            })
            ->values(); // Reset keys to 0, 1, 2, ...

        return response()->json($notifications);
    }

    // Helper method to get the icon based on the action
    private function getNotificationIcon($action)
    {
        switch ($action) {
            case 'admired':
                return 'favorite';
            case 'liked':
                return 'thumb_up';
            case 'commented':
                return 'comment';
            default:
                return 'notifications';
        }
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
