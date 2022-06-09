<?php

namespace Modules\EmailTemplate\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "subject" => $this->subject,
            "content" => $this->content,
            "style" => $this->style,
            "email_template_code" => $this->email_template_code,
            "is_system_defined" => (bool) $this->is_system_defined,
            "created_at" => $this->created_at->format("M d, Y H:i A")
        ];
    }
}
