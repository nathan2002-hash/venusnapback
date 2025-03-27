<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{

    protected $fillable = ['user_id', 'ip_address', 'duration', 'post_media_id', 'user_agent', 'device_info'];

    public function postmedia(){
        return $this->belongsTo(PostMedia::class, 'post_media_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
