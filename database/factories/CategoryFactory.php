<?php

namespace Database\Factories;

use App\Models\Category;
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
        $name = $this->faker->unique()->words(2, true);

        return [
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'parent_category_id' => null,
            'status' => $this->faker->boolean(90),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            $languages = Language::query()
                ->where('active', 1)
                ->pluck('code')
                ->filter()
                ->all();

            if (empty($languages)) {
                $languages = [config('app.locale', 'en')];
            }

            foreach ($languages as $languageCode) {
                $category->translations()->create([
                    'language_code' => $languageCode,
                    'name' => ucfirst(fake()->words(2, true)),
                    'description' => fake()->sentence(),
                    'image_url' => 'assets/images/placeholder-promo.svg',
                ]);
            }
        });
    }
}
