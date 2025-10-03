<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('site_settings')->updateOrInsert(
            ['id' => 1],
            [
                'site_name' => 'Velstore',
                'tagline' => 'Elevating your commerce experience',
                'meta_title' => 'Velstore — Modern eCommerce for ambitious teams',
                'meta_description' => 'Velstore is a demo storefront showcasing robust Laravel patterns, modular components, and a delightful admin experience.',
                'meta_keywords' => 'velstore, ecommerce, laravel, admin panel',
                'logo' => 'images/logo.svg',
                'favicon' => 'images/favicon.ico',
                'contact_email' => 'hello@velstore.io',
                'support_email' => 'support@velstore.io',
                'contact_phone' => '+1 (800) 555-0199',
                'support_hours' => 'Mon – Fri, 9:00 AM – 6:00 PM (UTC)',
                'address' => '123 Market Street, Suite 42, Commerce City',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6610f2',
                'facebook_url' => 'https://facebook.com/velstore',
                'twitter_url' => 'https://twitter.com/velstore',
                'instagram_url' => 'https://instagram.com/velstore',
                'linkedin_url' => 'https://linkedin.com/company/velstore',
                'maintenance_mode' => false,
                'maintenance_message' => 'We are performing scheduled maintenance. Please check back soon!',
                'footer_text' => '© ' . now()->year . ' Velstore. Crafted with Laravel and Tailwind.',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
