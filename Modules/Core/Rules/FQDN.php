<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;

class FQDN implements Rule
{

    public function __construct()
    {

    }

    public function passes($attribute, $value): bool
    {
        return preg_match('/(?=^.{1,254}$)(^(?:(?!\d|-)[a-z0-9\-]{1,63}(?<!-)\.)+(?:[a-z]{2,})$)/i', $value);
    }

    public function message(): string
    {
        return 'Invalid Hostname';
    }
}
