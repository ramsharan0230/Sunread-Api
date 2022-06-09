<?php

namespace Modules\Tax\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxRateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "country" => $this->whenLoaded("country"),
            "region" => $this->whenLoaded("region"),
            "identifier" => $this->identifier,
            "use_zip_range" => (bool) $this->use_zip_range,
            "zip_code" => $this->zip_code,
            "postal_code_from" => $this->postal_code_from,
            "postal_code_to" => $this->postal_code_to,
            "tax_rate" => (float) $this->tax_rate,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
