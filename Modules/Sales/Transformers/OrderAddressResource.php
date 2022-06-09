<?php

namespace Modules\Sales\Transformers;

use Exception;
use Modules\Country\Transformers\CityResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Country\Transformers\RegionResource;
use Modules\Country\Transformers\CountryResource;

class OrderAddressResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            "id" => $this->id,
            "first_name" => $this->first_name,
            "middle_name" => $this->middle_name,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "phone" => $this->phone,
            "email" => $this->email,
            "address1" => $this->address1,
            "address2" => $this->address2,
            "address3" => $this->address3,
            "postcode" => $this->postcode,
            "vat_number" => $this->vat_number,
        ];
        return array_merge($data, $this->countryRelatedData($request));
    }

    private function countryRelatedData(object $request): array
    {
        try
        {
            $data = [];
            if ($request->channel_id == $this->channel_id && $request->store_id == $this->store_id) {
                $data = [
                    "country" => new CountryResource($this->whenLoaded("country")),
                    "city" => new CityResource($this->whenLoaded("city")),
                    "region" => new RegionResource($this->whenLoaded("region")),
                    "city_name" => $this->city_name,
                    "region_name" => $this->region_name,
                    "country_id" => $this->country_id,
                    "city_id" => $this->city_id,
                    "region_id" => $this->region_id,
                ];
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
