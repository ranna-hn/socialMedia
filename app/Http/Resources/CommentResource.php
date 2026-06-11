<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'user' => new UserResource($this->whenLoaded('user')),
            'can_delete' => $user && (
                $user->isAdmin()
                || $user->id === $this->user_id
                || $user->id === $this->post?->user_id
            ),
        ];
    }
}
