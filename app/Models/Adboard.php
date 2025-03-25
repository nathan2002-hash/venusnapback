<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adboard extends Model
{
    protected $fillable = ['name', 'description', 'points', 'status', 'album_id'];

    public function album(){
        return $this->belongsTo(Album::class);
    }

    public function ad(){
        return $this->hasMany(Ad::class);
    }
}
