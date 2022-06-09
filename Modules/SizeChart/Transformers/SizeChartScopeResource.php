<?php
namespace Modules\SizeChart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeChartScopeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "size_chart" => new SizeChartResource($this->whenLoaded("size_chart")),
            "scope" => $this->scope,
            "scope_id" => $this->scope_id,
        ];
    }
}