<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'file_path', 'file_path_compress', 'status', 'sequence_order'];

    public function post(){
        return $this->belongsTo(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function admires(){
        return $this->hasMany(Admire::class);
    }

    public function views(){
        return $this->hasMany(View::class);
    }
}
