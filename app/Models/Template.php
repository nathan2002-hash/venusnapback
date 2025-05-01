<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['compressed_template', 'original_template', 'name', 'description', 'type', 'author', 'user_id', 'status',
    ];
}
