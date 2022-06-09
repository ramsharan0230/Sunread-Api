<?php

namespace Modules\SizeChart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\WebsiteResource;
use Modules\SizeChart\Transformers\SizeChartScopeResource;

class SizeChartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "slug" => $this->slug,
            "title" => $this->title,
            "content" => $this->content,
            "status" => (bool) $this->status,
            "created_at" => $this->created_at->format('M d, Y H:i A'),
            "website" => new WebsiteResource($this->whenLoaded("website")),
            "scopes" => SizeChartScopeResource::collection($this->whenLoaded("size_chart_scopes")),
            "size_chart_contents" => SizeChartContentResource::collection($this->whenLoaded("size_chart_contents")),
        ];
    }
}
