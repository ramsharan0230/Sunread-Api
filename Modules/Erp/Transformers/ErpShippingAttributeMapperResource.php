<?php

namespace Modules\Erp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ErpShippingAttributeMapperResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "shipping_agent_code" => $this->shipping_agent_code,
            "shipping_agent_service_code" => $this->shipping_agent_service_code,
            "shipping_method_code" => $this->shipping_method_code,
            "created_at" => $this->created_at->format("M d, Y H:i A"),
        ];
    }
}
