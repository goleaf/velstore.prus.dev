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
            'button_text' => $this->faker->optional(0.7)->words(2, true),
            'button_url' => $this->faker->optional(0.6)->url(),
            'image_url' => $this->faker->imageUrl(),
        ];
    }
}
