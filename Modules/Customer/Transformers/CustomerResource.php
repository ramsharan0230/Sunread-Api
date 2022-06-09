<?php

namespace Modules\Customer\Transformers;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Core\Transformers\StoreResource;
use Modules\Core\Transformers\WebsiteResource;

class CustomerResource extends JsonResource
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
            "full_name" => $this->name,
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "last_name" => $this->last_name,
            "email" => $this->email,
            "group" => new CustomerGroupResource($this->whenLoaded('group')),
            "website" => new WebsiteResource($this->whenLoaded('website')),
            "store" => new StoreResource($this->whenLoaded('store')),
            "profile_image" => $this->profile_image_url,
            "date_of_birth" => $this->date_of_birth,
            "gender" => $this->gender,
            "tax_number" => $this->tax_number,
            "subscribed_to_news_letter" => $this->subscribed_to_news_letter,
            "status" => (bool) $this->status,
            "is_lock" => (bool) $this->is_lock,
            "last_login_at" => ($this->last_login_at) ? $this->last_login_at->format('M d, Y H:i A') : "Never Logged in",
            "account_type" => $this->account_type,
            "phone" => $this->phone,
            "created_at" => $this->created_at->format('M d, Y H:i A')
        ];
    }
}
