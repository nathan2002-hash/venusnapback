<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $fillable = [
        'type',
        'subject',
        'body',
        'recipient_type',
        'user_id',
        'album_id',
        'attachment_path',
        'status',
        'sent_by',
        'sms_provider'
    ];
}
