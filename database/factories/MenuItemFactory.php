<?php

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        $title = $this->faker->words(2, true);

        return [
            'menu_id' => Menu::factory(),
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'order_number' => $this->faker->numberBetween(1, 20),
            'parent_id' => null,
        ];
    }
}
