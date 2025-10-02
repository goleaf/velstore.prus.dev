<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\BrandTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrandTranslation>
 */
class BrandTranslationFactory extends Factory
{
    protected $model = BrandTranslation::class;

    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'locale' => $this->faker->unique()->languageCode(),
            'name' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
        ];
    }
}
