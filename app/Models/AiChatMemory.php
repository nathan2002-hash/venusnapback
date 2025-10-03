<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatMemory extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'memory_type',
        'content',
        'metadata',
        'importance',
        'last_accessed_at',
        'expires_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope for active memories (not expired)
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Scope by memory type
    public function scopeOfType($query, $type)
    {
        return $query->where('memory_type', $type);
    }

    // Scope by importance
    public function scopeImportant($query, $minImportance = 5)
    {
        return $query->where('importance', '>=', $minImportance);
    }
}
