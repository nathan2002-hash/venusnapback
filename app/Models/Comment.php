<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public function postmedia(){
        return $this->belongsTo(PostMedia::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function commentreplies(){
        return $this->hasMany(CommentReply::class);
    }
}
