<?php

namespace App\Http\Resources;

use App\Models\GroupUser;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_null($this->resource) || $this->resource instanceof MissingValue) {
            return [];
        }

        $user = $request->user();
        $membership = $this->whenLoaded('memberships', fn () => $this->memberships->firstWhere('user_id', $user?->id));
        $membershipStatus = $membership instanceof GroupUser ? $membership->status : null;
        $membershipRole = $membership instanceof GroupUser ? $membership->role : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'about' => $this->about,
            'auto_approval' => $this->auto_approval,
            'member_count' => $this->member_count,
            'cover_url' => $this->cover_path ? Storage::url($this->cover_path) : '/storage/cover7.jpg',
            'has_cover' => (bool) $this->cover_path,
            'thumbnail_url' => $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'membership_status' => $membershipStatus,
            'membership_role' => $membershipRole,
            'is_admin' => $this->isAdmin($user),
            'url' => route('groups.show', $this->slug),
        ];
    }
}
