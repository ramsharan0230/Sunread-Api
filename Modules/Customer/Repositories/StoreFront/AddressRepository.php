<?php

namespace Modules\Customer\Repositories\StoreFront;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Core\Facades\SiteConfig;
use Modules\Core\Repositories\BaseRepository;
use Modules\Country\Entities\City;
use Modules\Country\Entities\Region;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerAddress;

class AddressRepository extends BaseRepository
{
    public function __construct(CustomerAddress $customer_address)
    {
        $this->model = $customer_address;
        $this->model_key = "customers.addresses";

        $this->rules = [
            "first_name" => "required|min:2|max:200",
            "middle_name" => "sometimes|nullable|min:2|max:200",
            "last_name" => "required|min:2|max:200",

            "address1" => "required|min:2|max:500",
            "address2" => "sometimes",
            "address3" => "sometimes",

            "country_id" => "required|exists:countries,id",
            "region_id" => "sometimes|nullable|exists:regions,id",
            "city_id" => "sometimes|nullable|exists:cities,id",
            "postcode" => "required",
            "phone" => "required",
            "vat_number" => "sometimes",
            "default_billing_address" => "sometimes|boolean",
            "default_shipping_address" => "sometimes|boolean",
            "region_name" => "sometimes",
            "city_name" => "sometimes",
            "channel_id" => "required|exists:channels,id"
        ];
    }

    public function regionAndCityRules(object $request, string $name): array
    {
        return [
            "{$name}.region_id" => "required_without:{$name}.region_name|exists:regions,id,country_id,{$request->{$name}["country_id"]}",
            "{$name}.city_id" => "required_without:{$name}.city_name",
            "{$name}.region_name" => "required_without:{$name}.region_id",
            "{$name}.city_name" => "required_without:{$name}.city_id",
        ];
    }

    public function checkShippingAddress(int $customer_id, int $channel_id): object
    {
        try
        {
            $address = $this->model->whereCustomerId($customer_id)->whereDefaultShippingAddress(1)->whereChannelId($channel_id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $address;
    }

    public function checkBillingAddress(int $customer_id, int $channel_id): object
    {
        try
        {
            $address = $this->model->whereCustomerId($customer_id)->whereDefaultBillingAddress(1)->whereChannelId($channel_id);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $address;
    }

    /**
     * create or update default address
     */
    public function createOrUpdateAddress(object $request, int $customer_id): array
    {
        try
        {
            $customer = Customer::findOrFail($customer_id);
            $core_cache = $this->getCoreCache($request);
            $channel_id = $core_cache->channel->id;

            $countries = $this->getCountry($core_cache);

            if($core_cache->website->id != $customer->website_id) throw ValidationException::withMessages([ "Country" => __("core::app.response.not-found", [ "name"=> "Country" ])]);

            if($request->shipping) {
                /**
                 * add channel id into request shipping address
                 */
                $shipping_channel = $request->request->get("shipping");
                $shipping_channel[ "channel_id" ] = $channel_id;
                $request->request->add([ "shipping" => $shipping_channel ]);

                $shipping = $this->checkShippingAddress($customer->id, $channel_id)->first();
                $data = $this->validateAddress($request, $customer, $countries, "shipping");

                $data = $this->checkRegionAndCity($data["shipping"], "shipping");

                if ($shipping) {
                    $created["shipping"] = $this->update($data, $shipping->id);
                } else {
                    $data["default_shipping_address"] = 1;
                    $data["default_billing_address"] = 0;
                    $created["shipping"] = $this->create($data);
                }
            }

            if($request->billing) {
                /**
                 * add channel id into request billing address
                 */
                $billing_channel = $request->request->get("billing");
                $billing_channel[ "channel_id" ] = $channel_id;
                $request->request->add([ "billing" => $billing_channel ]);

                $billing = $this->checkBillingAddress($customer->id, $channel_id)->first();
                $data = $this->validateAddress($request, $customer, $countries, "billing");

                $data = $this->checkRegionAndCity($data["billing"], "billing");
                if ($billing) {
                    $created["billing"] = $this->update($data, $billing->id);
                }
                else {
                    $data["default_shipping_address"] = 0;
                    $data["default_billing_address"] = 1;
                    $created["billing"] = $this->create($data);
                }
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $created;
    }

    /**
     * check region and city exist on country
     * Set value according to country
    */
    public function checkRegionAndCity(array $data, string $name): array
    {
        try
        {
            if(isset($data["region_id"])) {
                $data["region_name"] = null;
                if(isset($data["city_id"])) $data["city_name"] = null;
                else {
                    $data["city_id"] = null;
                    $cities = City::whereRegionId($data["region_id"])->count();
                    if($cities > 0) throw ValidationException::withMessages([ "city_id" => __("core::app.response.please-choose", [ "name"=> "{$name} City" ])]);
                }
            }
            else {
                $data["region_id"] = null;
                $data["city_id"] = null;
                $region = Region::whereCountryId($data["country_id"])->count();
                if($region > 0) throw ValidationException::withMessages([ "region_id" => __("core::app.response.please-choose", [ "name"=> "{$name} Region" ])]);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     * get channel related country list
    */
    public function getCountry(object $core_cache): object
    {
        try
        {
            if (!$core_cache->channel) throw ValidationException::withMessages([ "country_id" => __("core::app.response.not-found", [ "name"=> "Country" ])]);

            $allow = SiteConfig::fetch("allow_countries", "channel", $core_cache->channel->id);
            $default[] = SiteConfig::fetch("default_country", "channel", $core_cache->channel->id);

            $fetched = $allow->merge($default);
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $fetched;
    }

    /**
     * get customer default addresses
    */
    public function getCustomerAddress(object $request, object $customer): array
    {
        try
        {
            $coreCache = $this->getCoreCache($request);
            $data["shipping"] = $this->checkShippingAddress($customer->id, $coreCache->channel->id)->first();
            $data["billing"] = $this->checkBillingAddress($customer->id, $coreCache->channel->id)->first();
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }

    /**
     * validation customer address
    */
    public function validateAddress(object $request, object $customer, object $countries, string $name): array
    {
        try
        {
            /** save validation rules to new rules */
            $new_rules = $this->rules;

            /** Country validation */
            if(!isset($request->{$name}["country_id"])) throw ValidationException::withMessages([ "country_id" => __("core::app.response.not-found", [ "name"=> "Country" ])]);
            if (!$countries->contains("id", $request->{$name}["country_id"])) throw ValidationException::withMessages([ "country_id" => __("core::app.response.invalid-country", [ "name"=> $name ])]);

            $this->rules = [];
            foreach($new_rules as $key => $value)
            {
                $this->rules[$name.'.'.$key] = $value;
            }

            /** validate data */
            $data = $this->validateData($request, array_merge($this->regionAndCityRules($request,$name)));
            $data[$name] = array_merge($data[$name], [ "customer_id" => $customer->id ]);

            /** reset validation rules */
            $this->rules = $new_rules;
        }
        catch (Exception $exception)
        {
            throw $exception;
        }

        return $data;
    }
}
