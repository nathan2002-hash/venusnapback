<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'file_path', 'sequence_order'];

    public function post(){
        return $this->belongsTo(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function admires(){
        return $this->hasMany(Admire::class);
    }
}
