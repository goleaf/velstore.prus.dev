<?php

namespace Database\Factories;

use App\Models\SocialMediaLink;
use App\Models\SocialMediaLinkTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialMediaLinkTranslation>
 */
class SocialMediaLinkTranslationFactory extends Factory
{
    protected $model = SocialMediaLinkTranslation::class;

    public function definition(): array
    {
        return [
            'social_media_link_id' => SocialMediaLink::factory(),
            'language_code' => $this->faker->unique()->languageCode(),
            'name' => $this->faker->words(2, true),
        ];
    }
}
