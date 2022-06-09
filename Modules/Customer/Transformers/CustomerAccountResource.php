<?php

namespace Modules\Customer\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            "id" => $this->id,
            "full_name" => $this->name,
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "last_name" => $this->last_name,
            "email" => $this->email,
            "gender" => $this->gender,
            "date_of_birth" => $this->date_of_birth,
            "addresses" => CustomerAddressResource::collection($this->whenLoaded("addresses")),
            "profile_image" => $this->profile_image_url,
            "subscribed_to_news_letter" => $this->subscribed_to_news_letter,
            "phone" => $this->phone,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
