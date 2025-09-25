<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReply extends Model
{
    protected $fillable = ['comment_id', 'reply', 'status', 'gif_id', 'type', 'gif_url', 'gif_provider', 'reply_as_album_id', 'attachment_path', 'attachment_type'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function comment(){
        return $this->belongsTo(Comment::class);
    }

    public function replyAsAlbum()
    {
        return $this->belongsTo(Album::class, 'reply_as_album_id');
    }
}
