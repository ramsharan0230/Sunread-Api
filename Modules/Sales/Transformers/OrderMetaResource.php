<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderMetaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "has_script" => ($this->meta_key == "klarna_response") ? true : false,
            "has_encrypted" => ($this->meta_key == "adyen_response") ? true : false,
            "meta_key" => $this->meta_key,
            "meta_value" => $this->meta_value,
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
        ];
    }
}
