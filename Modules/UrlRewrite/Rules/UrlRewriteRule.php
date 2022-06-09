<?php

namespace Modules\UrlRewrite\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Category\Entities\Category;
use Modules\Product\Entities\Product;

class UrlRewriteRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $type; 

    public function __construct($type)
    {
        $this->type = $type;
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

        if($this->type == "Product") return (bool) Product::whereId($value)->first();

        if($this->type == "Category") return (bool) Category::whereId($value)->first();

        return false;
    }
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "{$this->type}  is not found";
    }
}
