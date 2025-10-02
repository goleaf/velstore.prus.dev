<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
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
            'slug' => Str::slug($this->faker->unique()->words(2, true)).'-'.$this->faker->numberBetween(100, 999),
            'parent_category_id' => null,
            'status' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category) {
            $languageCodes = Language::where('active', true)->pluck('code');

            if ($languageCodes->isEmpty()) {
                $languageCodes = collect([config('app.locale', 'en')]);
            }

            $translations = $languageCodes->map(fn ($code) => CategoryTranslation::factory()->make([
                'category_id' => $category->id,
                'language_code' => $code,
            ]));

            $category->translations()->saveMany($translations);
        });
    }
}
