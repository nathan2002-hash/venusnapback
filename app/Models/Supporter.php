<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supporter extends Model
{
    protected $fillable = ['post_id', 'album_id', 'user_id', 'status'];

    public function users(){
        return $this->hasMany(Supporter::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function album(){
        return $this->belongsTo(Album::class);
    }
}
