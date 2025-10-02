<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'seller_id' => null,
            'shop_id' => null,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'currency' => $this->faker->currencyCode(),
            'SKU' => strtoupper($this->faker->unique()->bothify('SKU-####')), 
            'weight' => $this->faker->randomFloat(2, 0.1, 10),
            'dimensions' => $this->faker->numberBetween(10, 100) . 'x' . $this->faker->numberBetween(10, 100) . 'x' . $this->faker->numberBetween(10, 100),
            'product_type' => $this->faker->randomElement(['simple', 'variable']),
            'image_url' => $this->faker->imageUrl(),
            'vendor_id' => null,
        ];
    }
}
