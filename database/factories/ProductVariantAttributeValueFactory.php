<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariantAttributeValue>
 */
class ProductVariantAttributeValueFactory extends Factory
{
    protected $model = ProductVariantAttributeValue::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'attribute_value_id' => AttributeValue::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
