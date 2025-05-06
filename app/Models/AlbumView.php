<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbumView extends Model
{
    protected $fillable = ['user_id', 'album_id', 'ip_address', 'device_info', 'user_agent'];
}
