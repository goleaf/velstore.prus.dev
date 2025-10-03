<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'status' => $this->faker->boolean(85),
            'type' => $this->faker->randomElement(['promotion', 'sale', 'seasonal', 'featured', 'announcement']),
        ];
    }
}
