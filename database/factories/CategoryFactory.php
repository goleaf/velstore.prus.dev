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
        $name = $this->faker->unique()->words(2, true);

        return [
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 9999),
            'status' => true,
            'parent_category_id' => null,
        ];
    }

    public function withTranslation(?string $languageCode = null): static
    {
        return $this->afterCreating(function (Category $category) use ($languageCode) {
            CategoryTranslation::factory()->create([
                'category_id' => $category->id,
                'language_code' => $languageCode ?? config('app.locale', 'en'),
            ]);
        });
    }
}
