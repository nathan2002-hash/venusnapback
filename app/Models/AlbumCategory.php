<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumCategory extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function albums(){
        return $this->hasMany(Album::class, 'album_category_id');
    }
}
