<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointManage extends Model
{
    protected $fillable = [
        'user_id',
        'manage_by',
        'points',
        'reason',
        'type',
        'status',
        'metadata',
        'balance_after',
        'description',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
