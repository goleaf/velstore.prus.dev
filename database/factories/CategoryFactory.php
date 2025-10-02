<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $slugBase = Str::slug($this->faker->unique()->words(2, true));

        return [
            'slug' => $slugBase,
            'parent_category_id' => null,
            'status' => $this->faker->boolean(80),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category) {
            $defaultLocale = config('app.locale');

            CategoryTranslation::factory()
                ->for($category)
                ->forLanguage($defaultLocale)
                ->create();
        });
    }
}
