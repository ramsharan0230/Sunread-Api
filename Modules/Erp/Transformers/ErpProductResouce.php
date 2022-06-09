<?php

namespace Modules\Erp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ErpProductResouce extends JsonResource
{
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
