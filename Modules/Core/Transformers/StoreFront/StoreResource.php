<?php

namespace Modules\Core\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Facades\SiteConfig;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "code" => $this->code,
            "locale" => SiteConfig::fetch("store_locale", "store", $this->id)?->code,
            "icon" => SiteConfig::fetch("store_icon", "store", $this->id)
        ];
    }
}
