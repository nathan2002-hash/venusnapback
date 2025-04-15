<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSupport extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'topic',
        'description',
        'priority',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
