<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
{
    public function toArray($request): array
    {
        $stores_count = $this->when($this->relationLoaded("channels"), function() {
            return $this->channels->first()?->relationLoaded("stores") ? $this->stores_count : 0;
        });

        return [
            "id" => $this->id,
            "code" => $this->code,
            "hostname" => $this->hostname,
            "name" => $this->name,
            "position" => $this->position,
            "description" => $this->description,
            "default_channel" => new ChannelResource($this->whenLoaded("default_channel")),
            "channels" => ChannelResource::collection($this->whenLoaded("channels")),
            "channels_count" => $this->when($this->relationLoaded("channels"), $this->channels->count()),
            "stores_count" => $this->when(($stores_count !== false), $stores_count),
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
