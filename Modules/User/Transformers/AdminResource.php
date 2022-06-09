<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->full_name,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "company" => $this->company,
            "address" => $this->address,
            "email" => $this->email,
            "status" => (bool) $this->status,
            "role" => new RoleResource($this->whenLoaded("role")),
            "profile_image" => $this->profile_image_url,
            "avatar" => $this->avatar,
            "is_invite" => (bool) $this->invitation_token,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
