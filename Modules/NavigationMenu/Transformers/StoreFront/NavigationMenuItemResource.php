<?php

namespace Modules\NavigationMenu\Transformers\StoreFront;

use Modules\Core\Facades\CoreCache;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Entities\Store;

class NavigationMenuItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $website = CoreCache::getWebsite($request->header("hc-host"));
        $channel = CoreCache::getChannel($website, $request->header("hc-channel"));
        $store = CoreCache::getStore($website, $channel, $request->header("hc-store"));
        $data = [
            "scope" => "store",
            "scope_id" => $store->id,
            "navigation_menu_item_id" => $this->id
        ];

        return  [
            "id" => $this->id,
            "title" => $this->value($data, "title"),
            "type" => $this->value($data, "type"),
            "background_type" => $this->value($data, "background_type"),
            "background_image" => $this->value($data, "background_image"),
            "background_video_type" => $this->value($data, "background_video_type"),
            "background_video" => $this->value($data, "background_video"),
            "background_overlay_color" => $this->value($data, "background_overlay_color"),
            "status" => (int) $this->value($data, "status"),
            "link" => $this->getFinalItemLink($store, $channel),
            "parent_id" => $this->parent_id,
            "position" => $this->position,
            "children" => NavigationMenuItemResource::collection($this->children),
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }

}
