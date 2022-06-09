<?php

namespace Modules\Core\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "hostname" => $this->hostname,
            "channels" => ChannelResource::collection($this->whenLoaded("channels")),
        ];
    }
}