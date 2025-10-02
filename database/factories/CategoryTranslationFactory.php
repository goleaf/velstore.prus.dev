<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryTranslation>
 */
class CategoryTranslationFactory extends Factory
{
    protected $model = CategoryTranslation::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'language_code' => $this->faker->unique()->lexify('??'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'image_url' => 'categories/'.$this->faker->unique()->lexify('image-????').'.jpg',
        ];
    }
}
