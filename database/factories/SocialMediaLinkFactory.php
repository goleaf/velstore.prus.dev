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
        $type = $this->faker->randomElement(['facebook', 'instagram', 'tiktok', 'youtube', 'x']);

        $platformNames = [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
            'x' => 'X',
        ];

        $domains = [
            'facebook' => 'facebook.com',
            'instagram' => 'instagram.com',
            'tiktok' => 'tiktok.com',
            'youtube' => 'youtube.com',
            'x' => 'x.com',
        ];

        return [
            'type' => $type,
            'platform' => $platformNames[$type] ?? ucfirst($type),
            'link' => 'https://www.' . ($domains[$type] ?? $type . '.com') . '/' . $this->faker->userName(),
        ];
    }
}
