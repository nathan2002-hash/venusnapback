<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AppMessage extends Model
{
     protected $fillable = [
        'type', 'title', 'content', 'image_path',
        'button_text', 'button_action', 'dismissible',
        'platforms', 'start_at', 'end_at'
    ];

    protected $casts = [
        'platforms' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('start_at', '<=', now())
            ->where('end_at', '>=', now());
    }

    /**
     * Relationship to user actions
     */
    public function userActions()
    {
        return $this->hasMany(AppMessageUserAction::class);
    }
}
