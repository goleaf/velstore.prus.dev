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
        $name = $this->faker->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'language_code' => 'en',
            'name' => ucfirst($name),
            'description' => $this->faker->sentence(),
            'image_url' => 'categories/' . $this->faker->unique()->lexify('image_?????') . '.jpg',
        ];
    }
}
