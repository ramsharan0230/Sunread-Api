<?php

namespace Modules\Customer\Transformers;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Tax\Transformers\CustomerTaxGroupResource;

class CustomerGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "slug" => $this->slug,
            "customer_tax_group_id" => new CustomerTaxGroupResource($this->whenLoaded("tax_group")),
            "is_user_defined" => $this->is_user_defined,
            "created_at" => Carbon::parse($this->created_at)->format('M j\\,Y H:i A')
        ];
    }
}
