<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use function config;

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
            'language_code' => config('app.locale', 'en'),
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
