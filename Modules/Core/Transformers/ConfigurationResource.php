<?php

namespace Modules\Core\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationResource extends JsonResource
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
            "scope" => $this->scope,
            "scope_id" => $this->scope_id,
            "path" => $this->path,
            "value" => $this->value,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
