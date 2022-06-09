<?php

namespace Modules\Product\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductGalleryRescouce extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "type" => $this->types()->pluck("slug")->toArray(),
            "delete" => 0,
            "background_color" => $this->background_color,
            "background_size" => $this->background_size,
            "image_background_color" => $this->image_background_color,
            "position" => $this->position,
            "url" => ($this->path) ? Storage::url($this->path) : "",
        ];
    }
}
