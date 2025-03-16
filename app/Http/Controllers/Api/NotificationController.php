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
    $user = Auth::user();

    $notifications = Notification::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'action' => $notification->action,
                'notifiable_type' => $notification->notifiable_type,
                'notifiable_id' => $notification->notifiable_id,
                'data' => json_decode($notification->data, true), // Ensure 'data' is decoded
                'group_count' => $notification->group_count,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at,
                'formatted_date' => Carbon::parse($notification->created_at)->format('M d, Y - h:i A'),
                'title' => ucfirst($notification->action),
                'icon' => $this->getNotificationIcon($notification->action),
            ];
        });

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
}
