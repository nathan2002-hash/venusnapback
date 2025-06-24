<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedRequest extends Model
{
    protected $fillable = ['ip', 'user_id', 'url', 'user_agent', 'status_code', 'attempts', 'last_attempt_at'];
}
