<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\BannerTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BannerTranslation>
 */
class BannerTranslationFactory extends Factory
{
    protected $model = BannerTranslation::class;

    public function definition(): array
    {
        return [
            'banner_id' => Banner::factory(),
            'language_code' => $this->faker->unique()->languageCode(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->imageUrl(),
            'type' => $this->faker->randomElement(['homepage', 'category', 'product']),
        ];
    }
}
