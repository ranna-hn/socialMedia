<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class PostAttachment extends Model
{
    use HasFactory;

    CONST UPDATED_AT = null;

    protected $fillable = [
        'post_id',
        'name' ,
        'path',
        'mime',
        'size',
        'created_by'
];

    protected static function boot()
    {
       parent::boot();


       static::deleted(function (self $model) {
               Storage::disk('public')->delete($model->path);
       });

    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(PhotoAlbum::class, 'album_post_attachment')
            ->withPivot('position')
            ->withTimestamps();
    }

}
