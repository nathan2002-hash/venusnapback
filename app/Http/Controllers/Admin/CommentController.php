<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReply;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function comments()
    {
        $comments = Comment::orderBy('created_at', 'desc')->paginate(100);
        return view('admin.comments.comment', [
           'comments' => $comments,
        ]);
    }

    public function replies()
    {
        $replies = CommentReply::orderBy('created_at', 'desc')->paginate(100);
        return view('admin.comments.replies', [
           'replies' => $replies,
        ]);
    }
}
