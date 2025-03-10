<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = ['title', 'description', 'source', 'user_id', 'status', 'user_agent', 'ip_address', 'device_info'];
}
