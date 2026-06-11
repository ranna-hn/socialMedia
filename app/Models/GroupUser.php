<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupUser extends Model
{
    use HasFactory;

    public const STATUS_APPROVED = 'approved';
    public const STATUS_PENDING = 'pending';
    public const STATUS_REJECTED = 'rejected';

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';

    public const UPDATED_AT = null;

    protected $fillable = [
        'status',
        'role',
        'token',
        'token_expires_date',
        'token_used',
        'user_id',
        'group_id',
        'created_by',
    ];

    protected $casts = [
        'token_expires_date' => 'datetime',
        'token_used' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
