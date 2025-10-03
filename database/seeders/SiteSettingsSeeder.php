<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SiteSetting::query()->updateOrCreate(
            ['id' => 1],
            [
                'site_name' => 'Velstore Demo',
                'tagline' => 'Modern commerce experiences for every brand.',
                'top_bar_message' => 'Free shipping on orders over $50 | Support | Store Locator',
                'meta_title' => 'Velstore Demo - Discover Products You Love',
                'meta_description' => 'Explore Velstore demo shop for beautifully curated collections, powerful commerce features, and a seamless shopping experience.',
                'meta_keywords' => 'velstore, ecommerce, demo shop, laravel commerce',
                'logo' => 'assets/images/logo-main.svg',
                'favicon' => 'favicon.ico',
                'contact_email' => 'support@velstore-demo.test',
                'contact_phone' => '+1 (555) 123-4567',
                'address' => '123 Commerce Street, Suite 400, Innovation City',
                'footer_text' => '© ' . now()->year . ' Velstore Demo. All rights reserved.',
                'facebook_url' => 'https://www.facebook.com/velstoredemo',
                'instagram_url' => 'https://www.instagram.com/velstoredemo',
                'twitter_url' => 'https://x.com/velstoredemo',
                'linkedin_url' => 'https://www.linkedin.com/company/velstoredemo',
            ]
        );

        Cache::forget('site_settings');
    }
}
