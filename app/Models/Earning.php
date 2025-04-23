<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    protected $fillable = ['album_id', 'batch_id', 'earning', 'meta', 'type', 'post_id', 'post_media_id'];
}
