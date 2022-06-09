<?php

namespace Modules\ClubHouse\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubHouseResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            "scope" => $request->scope ?? "website",
            "scope_id" => $request->scope_id ?? $this->website_id,
            "club_house_id" => $this->id
        ];

        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "position" => $this->position,
            "type" => $this->value($data, "type"),
            "title" => $this->value($data, "title"),
            "address" => $this->value($data, "address"),
            "status" => $this->value($data, "status"),
            "values" => $this->values,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
