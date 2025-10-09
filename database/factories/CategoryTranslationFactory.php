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
        $defaultLocale = config('app.locale');

        do {
            $languageCode = $this->faker->languageCode();
        } while ($languageCode === $defaultLocale);

        return [
            'category_id' => Category::factory(),
            'language_code' => $languageCode,
            'name' => $this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'image_url' => 'categories/'.$this->faker->unique()->lexify('??????????').'.jpg',
        ];
    }
}
