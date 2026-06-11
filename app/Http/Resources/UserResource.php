<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Storage;


class UserResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        if (is_null($this->resource) || $this->resource instanceof MissingValue) {
            return [];
        }

        $authUser = $request->user();
        $attributes = $this->resource->getAttributes();
        $isCurrentUser = array_key_exists('is_current_user', $attributes)
            ? (bool) $attributes['is_current_user']
            : (bool) ($authUser && $authUser->id === $this->id);
        $isFollowing = array_key_exists('is_following', $attributes)
            ? (bool) $attributes['is_following']
            : ((bool) ($authUser && ! $isCurrentUser) ? $authUser->isFollowing($this->resource) : false);

        return [
            "id" => $this->id,
            "name"=> $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "username" => $this->username,
            "role" => $this->role,
            "is_admin" => $this->isAdmin(),
            "followers_count" => $this->followers_count ?? 0,
            "following_count" => $this->following_count ?? 0,
            "cover_url"=> $this->cover_path ? Storage::url($this->cover_path) : null,
            "avatar_url" => $this->avatar_path ? Storage::url($this->avatar_path) : null,
            "profile_url" => route('profile', $this->username),
            "is_current_user" => $isCurrentUser,
            "is_following" => $isFollowing,
            ];
    }
}
