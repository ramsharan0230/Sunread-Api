<?php

namespace Modules\Review\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewReplyResource extends JsonResource
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
            "description" => $this->description,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
