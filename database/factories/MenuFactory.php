<?php

namespace Database\Factories;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(2, true),
            'status' => $this->faker->boolean(90),
            'date' => Carbon::instance($this->faker->dateTimeThisYear()),
        ];
    }
}
