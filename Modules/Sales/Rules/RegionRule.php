<?php

namespace Modules\Sales\Rules;

use Illuminate\Contracts\Validation\Rule;

class RegionRule implements Rule
{
    protected $address, $name;

    public function __construct(array $address)
    {
        $this->address = $address;
    }

    public function passes(mixed $attribute, mixed $value): bool
    {
        foreach ($this->address as $row) {
            if (!array_key_exists("region_id", $row) && !isset($row['region_name'])) {
                $this->name =  "region name";
                return false;
            } elseif (!array_key_exists("city_id", $row) && !isset($row['city_name'])) {
                $this->name =  "city name";
                return false;
            } else { 
                continue;
            }
        }
        
        return true;
    }

    public function message(): string
    {
        return "{$this->name} is required";
    }
}
