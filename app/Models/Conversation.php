<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
     protected $fillable = [
        'user_number',
        'our_number',
        'type',
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Scope for WhatsApp conversations
     */
    public function scopeWhatsApp($query)
    {
        return $query->where('type', 'whatsapp');
    }

    /**
     * Scope for SMS conversations
     */
    public function scopeSms($query)
    {
        return $query->where('type', 'sms');
    }
}
