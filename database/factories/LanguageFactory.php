<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $code = Str::lower($this->faker->unique()->lexify('??'));

        return [
            'name' => $this->faker->languageCode(),
            'code' => $code,
            'translated_text' => $this->faker->words(3, true),
            'active' => true,
        ];
    }
}
