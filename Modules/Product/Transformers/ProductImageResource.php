<?php

namespace Modules\Product\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "position" => $this->position,
            "path" => $this->path,
            "main_image" => $this->main_image,
            "small_image" => $this->small_image_url,
            "thumbnail" => $this->thumbnail_url,
            "created_at" => Carbon::parse($this->created_at)->format('M j\\,Y H:i A')
        ];
    }
}
