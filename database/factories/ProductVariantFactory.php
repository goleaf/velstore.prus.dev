<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'product_id' => Product::factory(),
            'variant_slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'price' => $this->faker->randomFloat(2, 10, 300),
            'discount_price' => $this->faker->optional(0.3)->randomFloat(2, 5, 250),
            'stock' => $this->faker->numberBetween(0, 100),
            'SKU' => strtoupper($this->faker->unique()->bothify('VAR-####')),
            'barcode' => $this->faker->ean13(),
            'is_primary' => $this->faker->boolean(20),
            'weight' => $this->faker->randomFloat(2, 0.1, 5),
            'dimensions' => $this->faker->numberBetween(5, 50) . 'x' . $this->faker->numberBetween(5, 50) . 'x' . $this->faker->numberBetween(5, 50),
        ];
    }
}
