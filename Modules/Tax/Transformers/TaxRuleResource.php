<?php

namespace Modules\Tax\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxRuleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "customer_tax_groups" => CustomerTaxGroupResource::collection($this->whenLoaded("customer_tax_groups")),
            "product_tax_groups" => ProductTaxGroupResource::collection($this->whenLoaded("product_tax_groups")),
            "tax_rates" => TaxRateResource::collection($this->whenLoaded("tax_rates")),
            "name" => $this->name,
            "priority" => $this->priority,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
