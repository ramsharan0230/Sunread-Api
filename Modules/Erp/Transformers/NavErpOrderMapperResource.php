<?php

namespace Modules\Erp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Country\Entities\Country;

class NavErpOrderMapperResource extends JsonResource
{
    public function toArray($request): array
    {
        return[
            "id" => $this->id,
            "website_id" => $this->website_id,
            "title" => $this->title,
            "country_name" => Country::find($this->country_id)->name,
            "nav_customer_number" => $this->nav_customer_number,
            "shipping_account" => $this->shipping_account,
            "discount_account" => $this->discount_account,
            "customer_price_group" => $this->customer_price_group,
            "is_default" => (bool) $this->is_default,
            "created_at" => $this->created_at->format("M d, Y H:i A"),
        ];
    }
}
