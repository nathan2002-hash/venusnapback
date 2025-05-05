<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaDownload extends Model
{
    protected $fillable = ['user_id', 'post_media_id', 'user_agent', 'device_info', 'ip_address'];
}
