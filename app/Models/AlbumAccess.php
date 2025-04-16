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

    // Relationship to the user who is requesting access
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship to the album being requested
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    // Relationship to the user who can grant access (album owner)
    public function granter()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
