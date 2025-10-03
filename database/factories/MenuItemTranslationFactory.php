<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItemTranslation>
 */
class MenuItemTranslationFactory extends Factory
{
    protected $model = MenuItemTranslation::class;

    public function definition(): array
    {
        return [
            'menu_item_id' => MenuItem::factory(),
            'language_code' => $this->faker->unique()->languageCode(),
            'title' => $this->faker->words(2, true),
        ];
    }
}
