<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeValueTranslation>
 */
class AttributeValueTranslationFactory extends Factory
{
    protected $model = AttributeValueTranslation::class;

    public function definition(): array
    {
        return [
            'attribute_value_id' => AttributeValue::factory(),
            'language_code' => $this->faker->unique()->languageCode(),
            'translated_value' => $this->faker->words(3, true),
        ];
    }
}
