<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageTranslation>
 */
class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'page_id' => Page::factory(),
            'language_code' => $this->faker->unique()->lexify('??'),
            'title' => $title,
            'content' => $this->faker->paragraph(),
            'image_url' => 'pages/'.Str::slug($title.'-'.$this->faker->unique()->numberBetween(1, 9999)).'.jpg',
        ];
    }
}
