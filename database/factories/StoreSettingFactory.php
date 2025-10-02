<?php

namespace Database\Factories;

use App\Models\StoreSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<StoreSetting>
 */
class StoreSettingFactory extends Factory
{
    protected $model = StoreSetting::class;

    public function definition(): array
    {
        return [
            'key' => Str::snake($this->faker->unique()->words(2, true)),
            'value' => $this->faker->sentence(),
        ];
    }
}
