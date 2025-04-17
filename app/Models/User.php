<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'country',
        'email',
        'password',
        'preference',
        'points',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function commentreplies(){
        return $this->hasMany(CommentReply::class);
    }

    public function supporters(){
        return $this->hasMany(Supporter::class);
    }

    public function albums(){
        return $this->hasMany(Album::class);
    }

    public function artworks(){
        return $this->hasMany(Artwork::class);
    }

    public function recommendations(){
        return $this->hasMany(Recommendation::class, 'user_id');
    }

    public function admires(){
        return $this->hasMany(Admire::class);
    }

    public function usersetting(){
        return $this->hasOne(UserSetting::class);
    }

    public function views(){
        return $this->hasMany(View::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

    public function payouts(){
        return $this->hasMany(Payout::class);
    }

    public function account(){
        return $this->hasOne(Account::class);
    }

    public function contactsupports()
    {
        return $this->hasMany(ContactSupport::class);
    }

     public function albumAccessRequests()
    {
        return $this->hasMany(AlbumAccess::class, 'user_id');
    }

    // Requests this user needs to approve (albums they own)
    public function albumAccessToApprove()
    {
        return $this->hasMany(AlbumAccess::class, 'granted_by');
    }

    // Albums this user owns
    public function ownedAlbums()
    {
        return $this->hasMany(Album::class, 'user_id');
    }

    // Albums this user has access to
    public function accessibleAlbums()
    {
        return $this->belongsToMany(Album::class, 'album_accesses', 'user_id', 'album_id')
            ->withPivot('status', 'role')
            ->wherePivot('status', 'approved');
    }

}
