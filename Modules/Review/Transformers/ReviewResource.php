<?php

namespace Modules\Review\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Customer\Transformers\CustomerResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "customer" => new CustomerResource($this->whenLoaded("customer")),
            "rating" => $this->rating,
            "title" => $this->title,
            "description" => $this->description,
            'votes' => $this->when($this->relationLoaded("review_votes"), [
                'positive_vote_count' => $this->positive_vote_count,
                'negative_vote_count' => $this->negative_vote_count,
                'visible_vote_count' => $this->visible_vote_count,
            ]),
            "review_replies" => ReviewReplyResource::collection($this->whenLoaded("review_replies")),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
