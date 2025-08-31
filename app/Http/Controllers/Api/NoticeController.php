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
                    'is_read' => false,
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

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'user_ids' => 'required|array',
        'user_ids.*' => 'exists:users,id',
        'is_important' => 'boolean',
        'action_url' => 'nullable|string',
        'action_text' => 'nullable|string',
        'scheduled_at' => 'nullable|date',
        'expires_at' => 'nullable|date',
    ]);

    try {
        // Create the notice
        $notice = Notice::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_important' => $request->is_important ?? false,
            'action_url' => $request->action_url,
            'action_text' => $request->action_text,
            'scheduled_at' => $request->scheduled_at,
            'expires_at' => $request->expires_at,
        ]);

        // Attach notice to specified users
        $userIds = $request->user_ids;
        $notice->users()->attach($userIds);

        // Dispatch notification job for each user
        foreach ($userIds as $userId) {
            SendMessageJob::dispatch(
                $userId,
                $request->title,
                $request->content,
                [
                    'action_url' => $request->action_url,
                    'action_text' => $request->action_text,
                    'is_important' => $request->is_important ?? false,
                    'notice_id' => $notice->id,
                ],
                $request->is_important ?? false
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notice created and notifications sent successfully',
            'notice' => $notice
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create notice: ' . $e->getMessage()
        ], 500);
    }
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
