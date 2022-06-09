<?php

namespace Modules\Country\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "region" =>  new RegionResource($this->whenLoaded("region")),
            "postal_code" => $this->postal_code,
            "code" => $this->code,
            "name" => $this->name,
            "created_at" => $this->created_at->format('M d, Y H:i A'),
        ];
    }
}
