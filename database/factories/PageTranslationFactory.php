<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageTranslation>
 */
class PageTranslationFactory extends Factory
{
    protected $model = PageTranslation::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'language_code' => $this->faker->unique()->lexify('??'),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraph(),
            'image_url' => 'pages/'.$this->faker->unique()->lexify('image-????').'.jpg',
        ];
    }
}
