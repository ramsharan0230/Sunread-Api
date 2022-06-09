<?php

namespace Modules\Customer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerViewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "last_logged_in" => $this->created_at->format('M d, Y H:i A'),
            "is_lock" => (bool) $this->is_lock,	
            "confirmed_email" => "",
            "created_at" => $this->created_at->format('M d, Y H:i A'),
            "customer_store" =>	$this->when($this->relationLoaded("store"), $this->store?->name),
            "customer_group" => $this->when($this->relationLoaded("group"), $this->group?->name),
            "default_addresses" => $this->when($this->relationLoaded("addresses"), function() {
                return [
                    "billing" => new CustomerAddressResource($this->default_billing_address),
                    "shipping" => new CustomerAddressResource($this->default_shipping_address)
                ];
            })
        ];
    }
}
