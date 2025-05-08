<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkShare extends Model
{
    protected $fillable = [
        'user_id', 'post_id', 'post_media_id',
        'share_method', 'share_url', 'short_code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function postMedia()
    {
        return $this->belongsTo(PostMedia::class);
    }

    public function visits()
    {
        return $this->hasMany(LinkVisit::class);
    }
}
