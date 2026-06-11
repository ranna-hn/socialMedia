<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->user),
            'group' => new GroupResource($this->whenLoaded('group')),
            'attachments' => PostAttachmentResource::collection($this->whenLoaded('attachments')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'num_of_reactions' => $this->reactions_count ?? $this->reactions()->count(),
            'num_of_comments' => $this->comments_count ?? $this->comments()->count(),
            'current_user_has_reaction' => $this->relationLoaded('reactions')
                ? $this->reactions->isNotEmpty()
                : $this->reactions()->where('user_id', $request->user()?->id)->exists(),
            'can_update' => $request->user()?->id === $this->user_id,
            'can_delete' => $request->user()?->id === $this->user_id
                || $request->user()?->isAdmin()
                || $this->group?->isAdmin($request->user()),
        ];
    }
}
