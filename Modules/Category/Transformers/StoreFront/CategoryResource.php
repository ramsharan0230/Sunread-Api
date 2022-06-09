<?php

namespace Modules\Category\Transformers\StoreFront;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Entities\Store;
use Modules\Core\Facades\CoreCache;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $store = Store::whereCode($request->header("hc-store"))->first();
        $data = [
            "scope" => "store",
            "scope_id" => $store->id
        ];

        return [
            "id" => $this->id,
            "slug" => $this->value($data, "slug"),
            "name" => $this->value($data, "name")
        ];
    }
}
