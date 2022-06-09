<?php

namespace Modules\ClubHouse\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\ClubHouse\Traits\HasScope;

class SlugUniqueRule implements Rule
{
    use HasScope;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $data, $club_house;

    public function __construct(object $data, ?object $club_house = null)
    {
        $this->data = $data;
        $this->club_house = $club_house;
    }

    /**
     * Determine if the validation rule passes.
     */
    public function passes($attribute, $value)
    {
        $arr = $this->data->toArray();
        return (bool) !$this->checkSlug($arr, $value, $this->club_house);
    }

    /**
     * Get the validation error message.
     */
    public function message()
    {
        return 'Slug has already been taken';
    }
}
