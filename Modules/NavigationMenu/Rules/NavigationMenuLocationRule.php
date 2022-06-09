<?php

namespace Modules\NavigationMenu\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\NavigationMenu\Entities\NavigationMenuItem;
use Modules\Core\Entities\Website;

class NavigationMenuLocationRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        $location_fields = collect(config("locations.locations"))->pluck('value')->toArray();
        if(in_array($value, $location_fields)) return true;
        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'Navigation Menu Item Location does not exist.';
    }
}
