<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'direction',
        'message_id',
        'text',
        'payload',
        'received_at',
        'user_id',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
