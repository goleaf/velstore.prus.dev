<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use function config;
use function fake;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 9999)),
            'parent_category_id' => null,
            'status' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            CategoryTranslation::factory()->create([
                'category_id' => $category->id,
                'language_code' => config('app.locale', 'en'),
                'name' => fake()->unique()->words(2, true),
                'description' => fake()->sentence(),
                'image_url' => fake()->imageUrl(),
            ]);
        });
    }
}
