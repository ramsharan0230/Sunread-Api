<?php

namespace Modules\Erp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ErpPaymentMethodMapperResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "payment_method" => $this->payment_method,
            "payment_method_code" => $this->payment_method_code,
            "created_at" => $this->created_at->format("M d, Y H:i A"),
        ];
    }
}
