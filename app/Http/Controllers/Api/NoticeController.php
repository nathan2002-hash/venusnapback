<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\Admin\SendMessageJob;

class NoticeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notices = $user->notices()
            ->active()
            ->withPivot('is_read', 'read_at')
            ->select('notices.*', 'user_notices.is_read', 'user_notices.read_at')
            ->orderBy('notices.created_at', 'desc')
            ->get()
            ->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'message' => $notice->content,
                    'is_important' => $notice->is_important,
                    'action_url' => $notice->action_url,
                    'action_text' => $notice->action_text,
                    'is_read' => (bool) $notice->pivot->is_read,
                    'formatted_date' => $notice->created_at->format('M j, Y \a\t g:i A'),
                    'created_at' => $notice->created_at,
                ];
            });

        return response()->json($notices);
    }

    public function show(Notice $notice)
    {
        $user = Auth::user();

        // Check if user has access to this notice
        if (!$user->notices()->where('notice_id', $notice->id)->exists()) {
            return response()->json(['error' => 'Notice not found'], 404);
        }

        $noticeData = [
            'id' => $notice->id,
            'title' => $notice->title,
            'message' => $notice->content,
            'is_important' => $notice->is_important,
            'action_url' => $notice->action_url,
            'action_text' => $notice->action_text,
            'is_read' => $notice->users()->where('user_id', $user->id)->first()->pivot->is_read,
            'formatted_date' => $notice->created_at->format('M j, Y \a\t g:i A'),
            'created_at' => $notice->created_at,
        ];

        return response()->json($noticeData);
    }

    public function markAsRead(Notice $notice)
    {
        $user = Auth::user();

        // Update the pivot table
        $user->notices()->updateExistingPivot($notice->id, [
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();

        $user->unreadNotices()->update([
            'user_notices.is_read' => true,
            'user_notices.read_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'All notices marked as read']);
    }
}
