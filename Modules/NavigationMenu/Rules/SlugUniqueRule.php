<?php

namespace Modules\NavigationMenu\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\NavigationMenu\Traits\HasScope;

class SlugUniqueRule implements Rule
{
    use HasScope;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $data, $navigation_menu_item;

    public function __construct(object $data, ?object $navigation_menu_item = null)
    {
        $this->data = $data;
        $this->navigation_menu_item = $navigation_menu_item;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        $arr = $this->data->toArray();
        return (bool) !$this->checkSlug($arr, $value, $this->navigation_menu_item);
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'Slug has already been taken';
    }
}
