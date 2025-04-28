<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenAi extends Model
{
    protected $fillable = ['user_id', 'provider', 'provider_credit', 'venusnap_points', 'file_path', 'file_path_compress',
    'original_description', 'edited_description', 'type'];
}
