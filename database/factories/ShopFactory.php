<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'vendor_id' => Vendor::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'logo' => $this->faker->imageUrl(),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
