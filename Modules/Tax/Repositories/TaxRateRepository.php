<?php

namespace Modules\Tax\Repositories;

use Exception;
use Illuminate\Validation\ValidationException;
use Modules\Tax\Entities\TaxRate;
use Modules\Core\Repositories\BaseRepository;
use Modules\Country\Entities\Region;

class TaxRateRepository extends BaseRepository
{
    public function __construct(TaxRate $TaxRate)
    {
        $this->model = $TaxRate;
        $this->model_key = "tax-rates";

        $this->rules = [
            "country_id" => "required|exists:countries,id",
            "region_id" => "sometimes|nullable|exists:regions,id",
            "identifier" => "required|unique:tax_rates,identifier",
            "use_zip_range" => "required|boolean",
            "zip_code" => "sometimes|nullable",
            "postal_code_from" => "sometimes|nullable",
            "postal_code_to" => "sometimes|nullable",
            "tax_rate" => "required|decimal"
        ];
    }

    public function validateRegionCountry(object $request): ?bool
    {
        try
        {
            if ( $request->region_id ) {
                $region = Region::whereId($request->region_id)->first();
                if ( $region->country_id !== $request->country_id) throw ValidationException::withMessages(["region_id" => __("core::app.response.country-not-found")]);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
        return true;
    }
}
