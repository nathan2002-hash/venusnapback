<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['name', 'description', 'tags', 'allow_comments', 'enable_rating', 'visibility',
      'user_id', 'thumbnail_original', 'business_logo_original', 'cover_image_compressed',
      'thumbnail_compressed', 'business_logo_compressed', 'cover_image_original', 'type',
      'phone', 'email', 'location', 'website', 'facebook', 'linkedin', 'is_paid_access', 'business_category'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function supporters(){
        return $this->hasMany(Supporter::class);
    }
}
