<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointManage extends Model
{
    public function user(){
        return $this->belongsTo(User::class);
    }
}
