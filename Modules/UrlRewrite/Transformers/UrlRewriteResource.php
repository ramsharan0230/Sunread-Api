<?php

namespace Modules\UrlRewrite\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UrlRewriteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "type" => $this->type,
            "type_attributes" => $this->type_attributes,
            "request_path" => $this->request_path,
            "target_path" => $this->target_path,
            "redirect_type" => $this->redirect_type,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
