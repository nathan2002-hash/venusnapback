<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['project_id', 'role', 'content', 'function_name', 'metadata'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
