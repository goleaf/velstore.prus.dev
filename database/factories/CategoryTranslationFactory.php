<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryTranslation>
 */
class CategoryTranslationFactory extends Factory
{
    protected $model = CategoryTranslation::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'language_code' => $this->faker->unique()->lexify('??'),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'image_url' => 'categories/' . $this->faker->unique()->uuid() . '.jpg',
        ];
    }
}
