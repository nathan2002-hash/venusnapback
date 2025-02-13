<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReply extends Model
{
    protected $fillable = ['comment_id', 'user_id', 'reply'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function comment(){
        return $this->belongsTo(Comment::class);
    }
}
