<?php

namespace Modules\Customer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDefaultAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "default_billing_address" => $this->when($this->default_billing_address, new CustomerAddressResource($this->default_billing_address)),
            "default_shipping_address" => $this->when($this->default_shipping_address, new CustomerAddressResource($this->default_shipping_address))
        ];
    }
}
