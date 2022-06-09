<?php

namespace Modules\Category\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Category\Traits\HasScope;

class SlugUniqueRule implements Rule
{
    use HasScope;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $data, $category; 

    public function __construct(object $data, ?object $category = null)
    {
        $this->data = $data;
        $this->category = $category;
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
        $arr = $this->data->toArray(); 
        if($this->category) $arr["parent_id"] = $this->category->parent_id;
        return (bool) !$this->checkSlug($arr, $value, $this->category);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Slug has already been taken';
    }
}
