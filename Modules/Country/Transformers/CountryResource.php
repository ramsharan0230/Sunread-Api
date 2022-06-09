<?php

namespace Modules\Country\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "iso_2_code" => $this->iso_2_code,
            "iso_3_code" => $this->iso_3_code,
            "numeric_code" => $this->numeric_code,
            "dialing_code" => $this->dialing_code,
            "name" => $this->name,
            "regions" => RegionResource::collection($this->whenLoaded("regions")),
            "created_at" => $this->created_at->format('M d, Y H:i A'),
        ];
    }
}
