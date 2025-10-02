<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Language;
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
        return [
            'slug' => Str::slug($this->faker->unique()->words(2, true)),
            'parent_category_id' => null,
            'status' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            $languages = Language::query()
                ->where('active', true)
                ->pluck('code');

            if ($languages->isEmpty()) {
                $languages = collect([config('app.locale', 'en')]);
            }

            foreach ($languages as $languageCode) {
                $category->translations()->create([
                    'language_code' => $languageCode,
                    'name' => fake()->words(2, true),
                    'description' => fake()->sentence(),
                    'image_url' => 'categories/'.Str::uuid().'.jpg',
                ]);
            }
        });
    }
}
