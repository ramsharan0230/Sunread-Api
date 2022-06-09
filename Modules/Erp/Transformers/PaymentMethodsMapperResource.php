<?php

namespace Modules\Erp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodsMapperResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "title" => ucwords(str_replace("_"," ", $this->name)),
            "slug" => $this->slug,
        ];
    }
}
