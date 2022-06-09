<?php

namespace Modules\Review\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\CustomerResource;

class ReviewVoteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "customer" => new CustomerResource($this->whenLoaded("customer")),
            "vote_type" => $this->vote_type,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
