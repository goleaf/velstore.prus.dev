<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\ProductVariantTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantTranslation>
 */
class ProductVariantTranslationFactory extends Factory
{
    protected $model = ProductVariantTranslation::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'language_code' => $this->faker->unique()->languageCode(),
            'name' => $this->faker->words(3, true),
        ];
    }
}
