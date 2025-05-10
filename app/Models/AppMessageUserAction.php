<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMessageUserAction extends Model
{
     protected $fillable = ['app_message_id', 'user_id', 'device_id', 'action', 'app_version', 'platform', 'ip'];
}
