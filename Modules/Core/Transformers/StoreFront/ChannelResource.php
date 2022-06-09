<?php

namespace Modules\Core\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Facades\SiteConfig;
use Modules\Core\Transformers\StoreFront\StoreResource;

class ChannelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "icon" => SiteConfig::fetch("channel_icon", "channel", $this->id),
            "default_store" => $this->default_store_id ? new StoreResource($this->whenLoaded("default_store")) : $this->default_store,
            "stores" => StoreResource::collection($this->whenLoaded("stores")),
        ];
    }
}
