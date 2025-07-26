<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemError extends Model
{
    protected $fillable = ['user_id', 'context', 'message', 'stack_trace', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];
}
