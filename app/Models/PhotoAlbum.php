<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PhotoAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(PostAttachment::class, 'album_post_attachment')
            ->withPivot('position')
            ->withTimestamps()
            ->orderBy('album_post_attachment.position')
            ->orderBy('album_post_attachment.id');
    }
}
