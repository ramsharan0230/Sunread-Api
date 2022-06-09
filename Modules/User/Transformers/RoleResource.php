<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "description" => $this->description,
            "permission_type" => $this->permission_type,
            "permissions" => $this->permission_type == "all" ? $this->getAllPermissions() : $this->permissions,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }

    private function getAllPermissions(): array
    {
        return array_map(function($item) {
            return $item["key"];
        }, config("acl"));
    }
}
