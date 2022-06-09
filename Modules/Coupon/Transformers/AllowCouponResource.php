<?php

namespace Modules\Coupon\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class AllowCouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "coupon" => new CouponResource($this->whenLoaded("coupon")),
            "model_type" => $this->model_type,
            "model" => $this->whenLoaded("model"),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
