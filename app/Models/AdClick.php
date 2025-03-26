<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdClick extends Model
{
    protected $fillable = ['ad_id', 'user_id', 'ad_session_id', 'points_used'];
}
