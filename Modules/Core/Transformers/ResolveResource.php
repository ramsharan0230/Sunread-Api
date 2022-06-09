<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ResolveResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "name" => $this->name,
            "channels" => ResolveChannelResource::collection($this->whenLoaded("channels")),
            "default_channel" => $this->when($this->default_channel, new ResolveChannelResource($this->default_channel)),
            "default_store" => $this->when($this->default_store, new ResolveStoreResource($this->default_store))
        ];
    }
}
