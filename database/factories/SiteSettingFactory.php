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
            'meta_title' => $this->faker->sentence(),
            'meta_description' => $this->faker->paragraph(),
            'meta_keywords' => implode(',', $this->faker->words(4)),
            'contact_email' => $this->faker->companyEmail(),
            'support_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'support_hours' => 'Mon – Fri, 9AM – 5PM',
            'address' => $this->faker->address(),
            'logo' => 'images/logo-' . $this->faker->word() . '.svg',
            'favicon' => 'images/favicon-' . $this->faker->word() . '.ico',
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6610f2',
            'facebook_url' => 'https://facebook.com/' . $this->faker->slug(),
            'twitter_url' => 'https://twitter.com/' . $this->faker->slug(),
            'instagram_url' => 'https://instagram.com/' . $this->faker->slug(),
            'linkedin_url' => 'https://linkedin.com/company/' . $this->faker->slug(),
            'maintenance_mode' => false,
            'maintenance_message' => $this->faker->sentence(),
            'footer_text' => $this->faker->sentence(),
        ];
    }
}
