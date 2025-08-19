<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    protected $fillable = ['user_id', 'dark_mode', 'history', 'push_notifications', 'tfa', 'fcm_token', 'email_notifications', 'sms_alert'];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
