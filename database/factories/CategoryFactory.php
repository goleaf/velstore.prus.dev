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
        $name = $this->faker->unique()->words(2, true);

        return [
            'slug' => Str::slug($name.'-'.$this->faker->unique()->numerify('###')),
            'status' => true,
            'parent_category_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Category $category): void {
            $faker = fake();

            $languageCodes = Language::query()
                ->where('active', true)
                ->pluck('code')
                ->all();

            if (empty($languageCodes)) {
                $languageCodes = [config('app.locale') ?? 'en'];
            }

            foreach ($languageCodes as $code) {
                $category->translations()->create([
                    'language_code' => $code,
                    'name' => $faker->words(2, true),
                    'description' => $faker->sentence(),
                    'image_url' => 'categories/'.$faker->unique()->lexify('image-????').'.jpg',
                ]);
            }
        });
    }
}
