<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    public function definition(): array
    {
        return [
            'site_name' => $this->faker->company(),
            'tagline' => $this->faker->catchPhrase(),
            'top_bar_message' => $this->faker->sentence(8),
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
            'meta_keywords' => implode(',', $this->faker->words(4)),
            'logo' => 'assets/images/logo-main.svg',
            'favicon' => 'favicon.ico',
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'footer_text' => $this->faker->sentence(),
            'facebook_url' => $this->faker->url(),
            'instagram_url' => $this->faker->url(),
            'twitter_url' => $this->faker->url(),
            'linkedin_url' => $this->faker->url(),
        ];
    }
}
