<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;


class UserResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }
        return [
            "id" => $this->id,
            "name"=> $this->name,
            "email" => $this->email,
            "email_verified_at" => $this->email_verified_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "username" => $this->username,
            "cover_url"=> Storage::url($this->cover_path),
            "avatar_url" => Storage::url($this->avatar_path)
            ];
    }
}
