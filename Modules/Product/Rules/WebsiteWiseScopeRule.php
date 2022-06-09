<?php

namespace Modules\Product\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Core\Entities\Website;

class WebsiteWiseScopeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $website_id, $website_model, $scope; 

    public function __construct($scope, $website_id)
    {
        $this->website_id = $website_id;
        $this->website_model = new Website();
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
        if($this->scope == "website") return (bool) $this->website_id == $value;

        if($this->scope == "channel")  return (bool) in_array($value, $this->website_model->find($this->website_id)->channels->pluck('id')->toArray());

        if($this->scope == "store")  return (bool) in_array($value, $this->website_model->find($this->website_id)->channels->map(function ($channel) {
            return $channel->stores->pluck('id');
        })->flatten(1)->toArray());

        return true;       
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Scope does not belongs to this product.';
    }
}
