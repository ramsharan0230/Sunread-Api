<?php

namespace Modules\Sales\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Transformers\AdminResource;

class OrderCommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "order" => new OrderResource($this->whenLoaded("order")),
            "user" => new AdminResource($this->whenLoaded("admin")),
            "comment" => $this->comment,
            "is_customer_notified" => (bool) $this->is_customer_notified,
            "is_visible_on_storefornt" => (bool) $this->is_visible_on_storefornt,
            "status_flag" => $this->status_flag,
            "created_at" => $this->created_at?->format("M d, Y H:i A"),
        ];
    }
}
