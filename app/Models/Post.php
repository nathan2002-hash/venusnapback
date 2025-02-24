<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['description', 'type', 'visibility'];

    public function postmedias(){
        return $this->hasMany(PostMedia::class);
    }

    public function saveds(){
        return $this->hasMany(Saved::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function recommendations(){
        return $this->hasMany(Recommendation::class);
    }
}
