<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['status', 'reason', 'post_media_id', 'user_id'];
}
