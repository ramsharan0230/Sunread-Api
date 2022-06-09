<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "source_currency" => $this->source_currency,
            "target_currency" => $this->target_currency,
            'source' => $this->whenLoaded("source"),
            'target' => $this->whenLoaded("target"),
            "rate" => $this->rate,
            "updated_at" => $this->created_at->format("M d, Y H:i A"),
            "created_at" => $this->updated_at->format("M d, Y H:i A")
        ];
    }
}
