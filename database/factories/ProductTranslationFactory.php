<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductTranslation>
 */
class ProductTranslationFactory extends Factory
{
    protected $model = ProductTranslation::class;

    public function definition(): array
    {
        $locale = $this->faker->unique()->languageCode();

        return [
            'product_id' => Product::factory(),
            'locale' => $locale,
            'language_code' => $locale,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'tags' => implode(',', $this->faker->words(3)),
        ];
    }
}
