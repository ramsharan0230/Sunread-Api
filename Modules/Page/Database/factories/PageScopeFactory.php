<?php
namespace Modules\Page\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Modules\Core\Entities\Store;
use Modules\Core\Entities\Website;

class PageScopeFactory extends Factory
{
    protected $model = \Modules\Page\Entities\PageScope::class;

    public function definition(): array
    {
        $data["scope"] = "store";
        $data["scope_id"] = 0;  
        return $data;
    }
}

