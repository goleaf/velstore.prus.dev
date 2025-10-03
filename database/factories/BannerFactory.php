<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        $startsAt = $this->faker->optional(0.6)->dateTimeBetween('-7 days', '+7 days');
        $endsAt = null;

        if ($startsAt) {
            $endsAt = $this->faker->optional(0.5)->dateTimeBetween($startsAt, '+3 weeks');
        } else {
            $endsAt = $this->faker->optional(0.3)->dateTimeBetween('now', '+3 weeks');
        }

        return [
            'title' => $this->faker->sentence(3),
            'status' => $this->faker->boolean(85),
            'type' => $this->faker->randomElement(['promotion', 'sale', 'seasonal', 'featured', 'announcement']),
            'display_location' => $this->faker->randomElement(['home', 'shop', 'category', 'product', 'global']),
            'priority' => $this->faker->numberBetween(0, 150),
            'starts_at' => $startsAt ? Carbon::instance($startsAt) : null,
            'ends_at' => $endsAt ? Carbon::instance($endsAt) : null,
        ];
    }
}
