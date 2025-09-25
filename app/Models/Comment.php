<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['post_media_id', 'user_id', 'comment', 'status', 'gif_id', 'type', 'gif_url', 'gif_provider', 'comment_as_album_id', 'attachment_path', 'attachment_type'];

    public function postmedia(){
        return $this->belongsTo(PostMedia::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function commentreplies(){
        return $this->hasMany(CommentReply::class);
    }

    public function commentAsAlbum()
    {
        return $this->belongsTo(Album::class, 'comment_as_album_id');
    }
}
