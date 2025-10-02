<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'slug' => Str::slug($this->faker->unique()->words(2, true)),
            'parent_category_id' => null,
            'status' => $this->faker->boolean(90),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category) {
            $languageCode = config('app.locale', 'en');

            if ($category->translations()->where('language_code', $languageCode)->doesntExist()) {
                CategoryTranslation::factory()->for($category)->create([
                    'language_code' => $languageCode,
                ]);
            }
        });
    }
}
