<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasSlug;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'cover_path',
        'avatar_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'followers_count' => 'integer',
            'following_count' => 'integer',
        ];
    }
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            // Code officiel Spatie pour fusionner plusieurs champs :
            ->generateSlugsFrom('name')
            ->saveSlugsTo('username')
            ->doNotGenerateSlugsonUpdate();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function photoAlbums(): HasMany
    {
        return $this->hasMany(PhotoAlbum::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_users')
            ->withPivot(['status', 'role', 'token', 'token_expires_date', 'token_used', 'created_by', 'created_at']);
    }

    public function approvedGroups(): BelongsToMany
    {
        return $this->groups()
            ->wherePivot('status', GroupUser::STATUS_APPROVED);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'user_id')
            ->withPivot('created_at');
    }

    public function followings(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'followed_id')
            ->withPivot('created_at');
    }

    public function followerRows(): HasMany
    {
        return $this->hasMany(Follower::class, 'followed_id');
    }

    public function followingRows(): HasMany
    {
        return $this->hasMany(Follower::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isFollowing(User $user): bool
    {
        return $this->followings()
            ->where('followed_id', $user->id)
            ->exists();
    }
}
