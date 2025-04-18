<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['description', 'type', 'status', 'visibility'];

    public function postmedias(){
        return $this->hasMany(PostMedia::class, 'post_id');
    }

    public function saveds(){
        return $this->hasMany(Saved::class);
    }

    public function album(){
        return $this->belongsTo(Album::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function recommendations(){
        return $this->hasMany(Recommendation::class, 'post_id');
    }
}
