<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'name' => $name,
            'description' => $this->faker->sentence(),
            'image_url' => 'categories/'.Str::slug($name.'-'.$this->faker->unique()->numberBetween(1, 9999)).'.jpg',
        ];
    }

    /**
     * Indicate that the translation should be created for a specific language.
     */
    public function forLanguage(string $languageCode): static
    {
        return $this->state(fn () => [
            'language_code' => $languageCode,
        ]);
    }
}
