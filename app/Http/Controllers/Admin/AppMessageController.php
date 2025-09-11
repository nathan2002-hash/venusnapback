<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\Admin\SendMessageJob;
use App\Jobs\Admin\ManualPostNotificationJob;
use Illuminate\Http\Request;
use App\Models\Notice;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class AppMessageController extends Controller
{
    public function create()
    {
        $users = User::where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'email']);

        $recentNotices = Notice::withCount('users')
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get();

        return view('admin.communication.appnotification', compact('users', 'recentNotices'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'recipient_type' => 'required|in:specific,all',
            'user_ids' => 'required_if:recipient_type,specific|array',
            'user_ids.*' => 'exists:users,id',
            'is_important' => 'boolean',
            'action_url' => 'nullable|string',
            'action_text' => 'nullable|string|max:50',
            'scheduled_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:scheduled_at',
            'send_push' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Create the notice
            $notice = Notice::create([
                'title' => $request->title,
                'content' => $request->content,
                'is_important' => $request->boolean('is_important'),
                'action_url' => $request->action_url === 'custom' ? $request->custom_url : $request->action_url,
                'action_text' => $request->action_text,
                'scheduled_at' => $request->scheduled_at,
                'expires_at' => $request->expires_at,
            ]);

            // Determine recipient user IDs
            $userIds = $request->recipient_type === 'all'
                ? User::where('status', 'active')->pluck('id')->toArray()
                : $request->user_ids;

            // Attach notice to users
            $notice->users()->attach($userIds);

            // Send push notifications if requested
            if ($request->boolean('send_push')) {
                foreach ($userIds as $userId) {
                    SendMessageJob::dispatch(
                        $userId,
                        $request->title,
                        $request->content,
                        [
                            'action_url' => $request->action_url === 'custom' ? $request->custom_url : $request->action_url,
                            'action_text' => $request->action_text,
                            'is_important' => $request->boolean('is_important'),
                            'notice_id' => $notice->id,
                        ],
                        $request->boolean('is_important')
                    );
                }
            }

            DB::commit();

            return redirect()->route('admin.notices.create')
                            ->with('success', 'Notice sent successfully to ' . count($userIds) . ' users.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Failed to send notice: ' . $e->getMessage());
        }
    }

    public function postnoticecreate(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'recipient_type' => 'required|in:specific,all,album_supporters',
            'user_ids' => 'required_if:recipient_type,specific|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_important' => 'nullable|boolean',
            'send_push' => 'nullable|boolean',
        ]);

        $post = Post::with(['user', 'album'])->findOrFail($request->post_id);

        // Determine recipients based on type
        $recipients = collect();

        if ($request->recipient_type === 'specific') {
            $recipients = User::whereIn('id', $request->user_ids)->get();
        } elseif ($request->recipient_type === 'all') {
            $recipients = User::where('id', '!=', $post->user_id)->get(); // Exclude post owner
        } elseif ($request->recipient_type === 'album_supporters' && $post->album) {
            $recipients = $post->album->supporters()->with('user')->get()->pluck('user');
            $recipients = $recipients->filter(function($user) use ($post) {
                return $user->id !== $post->user_id;
            });
        }

        // Dispatch job for each recipient
        foreach ($recipients as $recipient) {
            ManualPostNotificationJob::dispatch(
                $post,
                $recipient,
                $request->title,
                $request->message,
                $request->boolean('is_important'),
                $request->boolean('send_push')
            );
        }

        return redirect()->back()->with('success', "Notification sent to {$recipients->count()} users.");
    }

    public function showPostNotificationForm()
    {
        $posts = Post::with('user')->latest()->take(100)->get();
        $users = User::where('status', 'active')->get();

        return view('admin.communication.postnotification', compact('posts', 'users'));
}
}
