<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $fillable = ['name', 'description', 'tags', 'allow_comments', 'enable_rating', 'visibility',
      'user_id', 'thumbnail_original', 'business_logo_original', 'cover_image_compressed', 'monetization_status',
      'thumbnail_compressed', 'business_logo_compressed', 'cover_image_original', 'type',
      'phone', 'email', 'location', 'website', 'facebook', 'linkedin', 'is_paid_access', 'business_category', 'album_category_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function supporters(){
        return $this->hasMany(Supporter::class);
    }

    public function adboards(){
        return $this->hasMany(Adboard::class);
    }

    public function sharedWith()
    {
        return $this->hasMany(AlbumAccess::class, 'album_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // All access requests for this album
    public function accessRequests()
    {
        return $this->hasMany(AlbumAccess::class);
    }

    // Approved access only
    public function approvedAccess()
    {
        return $this->hasMany(AlbumAccess::class)
            ->where('status', 'approved');
    }

    // Pending requests only
    public function pendingAccess()
    {
        return $this->hasMany(AlbumAccess::class)
            ->where('status', 'pending');
    }

    public function albumcategory()
    {
        return $this->belongsTo(AlbumCategory::class);
    }

    public function monetizationrequests()
    {
        return $this->hasMany(MonetizationRequest::class);
    }
}
