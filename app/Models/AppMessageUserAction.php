<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMessageUserAction extends Model
{
    protected $fillable = ['app_message_id', 'user_id', 'device_id', 'action', 'app_version', 'platform', 'ip'];

    public function appMessage()
    {
        return $this->belongsTo(AppMessage::class);
    }

    /**
     * Relationship to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
