<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admire extends Model
{
    protected $fillable = ['user_id', 'post_media_id'];

    public function postmedia(){
        return $this->belongsTo(PostMedia::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
