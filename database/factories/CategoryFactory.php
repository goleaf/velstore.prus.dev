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

    /**
     * Create a translation for the category after it has been persisted.
     */
    public function withTranslation(?string $languageCode = null): static
    {
        return $this->afterCreating(function (Category $category) use ($languageCode) {
            $language = $languageCode ?? config('app.locale');

            if ($category->translations()->where('language_code', $language)->exists()) {
                return;
            }

            CategoryTranslation::factory()
                ->for($category)
                ->forLanguage($language)
                ->create();
        });
    }
}
