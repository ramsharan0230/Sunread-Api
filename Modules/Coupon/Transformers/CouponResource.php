<?php

namespace Modules\Coupon\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "name" => $this->name,
            "description" => $this->description,
            "valid_from" => Carbon::parse($this->created_at)->format('M j,Y'),
            "valid_to" => Carbon::parse($this->created_at)->format('M j,Y'),

            "flat_discount_amount" => $this->flat_discount_amount,
            "min_discount_amount" => $this->min_discount_amount,
            "max_discount_amount" => $this->max_discount_amount,
            "min_purchase_amount" => $this->min_purchase_amount,
            "discount_percent" => $this->discount_percent,
            "max_uses" => $this->max_uses,
            "single_user_uses" => $this->single_user_uses,
            "only_new_user" => $this->only_new_user,
            "scope_public" => $this->scope_public,
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
