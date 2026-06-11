<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoAlbumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'photos_count' => $this->photos_count ?? $this->whenLoaded('photos', fn () => $this->photos->count(), 0),
            'photos' => PostAttachmentResource::collection($this->whenLoaded('photos')),
        ];
    }
}
