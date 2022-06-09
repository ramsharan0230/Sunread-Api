<?php

namespace Modules\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Core\Entities\Channel;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;

class ScopeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $scope;

    public function __construct($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($this->scope == "global") return ($value == 0);

        if($this->scope == "website") return (bool) Website::whereId($value)->first();

        if($this->scope == "channel")  return (bool) Channel::whereId($value)->first();

        if($this->scope == "store")  return (bool) Store::whereId($value)->first();

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Scope Id doesnt exists';
    }
}
