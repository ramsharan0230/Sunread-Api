<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "code" => $this->code,
            "name" => $this->name,
            "symbol" => $this->symbol,
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
