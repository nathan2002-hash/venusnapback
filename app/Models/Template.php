<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'name', 'type', 'author', 'user_id', 'description',
        'original_template', 'compressed_template', 'status'
    ];
}
