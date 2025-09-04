<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    protected $fillable = ['user_id', 'status', 'file_path', 'content', 'background_color',
    'thumbnail', 'content_color', 'background_image', 'refine_prompt'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
