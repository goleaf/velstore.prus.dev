<?php

namespace Database\Factories;

use App\Models\SocialMediaLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMediaLink>
 */
class SocialMediaLinkFactory extends Factory
{
    protected $model = SocialMediaLink::class;

    public function definition(): array
    {
        $platform = $this->faker->randomElement(['facebook', 'twitter', 'instagram', 'linkedin']);

        return [
            'type' => $this->faker->randomElement(['primary', 'secondary']),
            'platform' => $platform,
            'link' => 'https://www.' . $platform . '.com/' . $this->faker->userName(),
        ];
    }
}
