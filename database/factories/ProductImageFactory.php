<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'image_url' => $this->faker->imageUrl(),
            'type' => $this->faker->randomElement(['thumb', 'slide']),
            'product_id' => Product::factory(),
        ];
    }
}
