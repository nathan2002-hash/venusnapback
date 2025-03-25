<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdMedia extends Model
{
    protected $fillable = ['ad_id', 'file_path', 'file_path_compress', 'sequence_order', 'status', 'type', 'object', 'description'];

    public function ad(){
        return $this->belongsTo(Ad::class);
    }
}
