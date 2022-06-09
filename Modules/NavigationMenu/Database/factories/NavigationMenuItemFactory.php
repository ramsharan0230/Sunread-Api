<?php
namespace Modules\NavigationMenu\Database\factories;

use Modules\Core\Entities\Website;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\NavigationMenu\Entities\NavigationMenu;

class NavigationMenuItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\NavigationMenu\Entities\NavigationMenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "navigation_menu_id" => NavigationMenu::factory()->create()->id,
        ];
    }
}

