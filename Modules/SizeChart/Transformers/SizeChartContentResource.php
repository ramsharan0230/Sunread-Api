<?php
namespace Modules\SizeChart\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeChartContentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "slug" => $this->slug,
            "content" => $this->content,
            "type" => $this->type,
            "created_at" => $this->created_at->format('M d, Y H:i A'),
        ];
    }
}