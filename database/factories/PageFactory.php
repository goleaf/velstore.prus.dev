<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        $status = $this->faker->boolean(80);

        return [
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 9999),
            'status' => $status,
            'template' => $this->faker->randomElement(['default', 'with-hero']),
            'show_in_navigation' => $this->faker->boolean(40),
            'show_in_footer' => $this->faker->boolean(60),
            'is_featured' => $this->faker->boolean(30),
            'published_at' => $status ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
        ];
    }
}
