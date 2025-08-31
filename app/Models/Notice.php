<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_important',
        'action_url',
        'action_text',
        'scheduled_at',
        'expires_at'
    ];

    protected $casts = [
        'is_important' => 'boolean',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_notices')
                    ->withPivot('is_read', 'read_at')
                    ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($query) {
            $query->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
        })->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', now());
        });
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

}
