<?php

namespace Modules\Category\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Entities\Website;

class WebsiteWiseCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $website = Website::whereHostname($request->host)->first();
        $data = [
            "scope" => "website",
            "scope_id" => $website->id
        ];
        return [
            "id" => $this->id,
            "slug" => $this->value($data, "slug"),
            "name" => $this->value($data, "name")
        ];
    }
}
