<?php

namespace Modules\ClubHouse\Transformers\StoreFront;

use Modules\Core\Facades\CoreCache;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Entities\Store;

class ClubHouseResource extends JsonResource
{
    public function toArray($request): array
    {

        $store = Store::whereCode($request->header("hc-store"))->first();
        $data = [
            "scope" => "store",
            "scope_id" => $store->id,
        ];

        return [
            "id" => $this->id,
            "website_id" => $this->website_id,
            "position" => $this->position,
            "type" => $this->value($data, "type"),
            "title" => $this->value($data, "title"),
            "slug" => $this->value($data, "slug"),
            "status" => $this->value($data, "status"),
            "hero_content" => $this->value($data, "hero_content"),
            "subtitle" => $this->value($data, "subtitle"),
            "thumbnail" => $this->value($data, "thumbnail"),
            "opening_hours" => $this->value($data, "opening_hours"),
            "address" => $this->value($data, "address"),
            "contact" => $this->value($data, "contact"),
            "latitude" => $this->value($data, "latitude"),
            "longitude" => $this->value($data, "longitude"),
            "background_type" => $this->value($data, "background_type"),
            "background_image" => $this->value($data, "background_image"),
            "background_video" => $this->value($data, "background_video"),
            "meta_title" => $this->value($data, "meta_title"),
            "meta_keywords" => $this->value($data, "meta_keywords"),
            "meta_description" => $this->value($data, "meta_description"),
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
