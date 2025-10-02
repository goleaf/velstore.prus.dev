<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'logo_url' => $this->faker->imageUrl(),
            'status' => $this->faker->boolean(90),
        ];
    }
}
