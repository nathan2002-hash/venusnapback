<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumAccess extends Model
{

    protected $fillable = ['user_id', 'album_id', 'granted_by', 'status', 'continent', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
