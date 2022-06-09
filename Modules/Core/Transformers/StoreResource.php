<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Facades\SiteConfig;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "position" => $this->position,
            "channel" => new ChannelResource($this->whenLoaded("channel")),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
