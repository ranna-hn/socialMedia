<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Group extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'cover_path',
        'thumbnail_path',
        'auto_approval',
        'member_count',
        'about',
        'user_id',
        'deleted_by',
    ];

    protected $casts = [
        'auto_approval' => 'boolean',
        'member_count' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupUser::class);
    }

    public function approvedMemberships(): HasMany
    {
        return $this->memberships()->where('status', GroupUser::STATUS_APPROVED);
    }

    public function pendingMemberships(): HasMany
    {
        return $this->memberships()->where('status', GroupUser::STATUS_PENDING);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_users')
            ->withPivot(['id', 'status', 'role', 'token', 'token_expires_date', 'token_used', 'created_by', 'created_at']);
    }

    public function approvedMembers(): BelongsToMany
    {
        return $this->members()
            ->wherePivot('status', GroupUser::STATUS_APPROVED);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class)->latest();
    }

    public function isAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $this->user_id === $user->id) {
            return true;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', GroupUser::STATUS_APPROVED)
            ->where('role', GroupUser::ROLE_ADMIN)
            ->exists();
    }

    public function membershipFor(?User $user): ?GroupUser
    {
        if (! $user) {
            return null;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->first();
    }
}
