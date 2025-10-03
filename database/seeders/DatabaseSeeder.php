<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            CurrencySeeder::class,
            SiteSettingsSeeder::class,
            ThemeSeeder::class,
            MenuSeeder::class,
            BannerSeeder::class,
            PageSeeder::class,
            AttributeSeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
            ProductVariantLocaleSeeder::class,
            ProductVariantDemoSeeder::class,
            ProductReviewSeeder::class,
            CouponSeeder::class,
            OrderSeeder::class,
            PaymentGatewaySeeder::class,
            PaymentGatewayConfigSeeder::class,
            PaymentSeeder::class,
            RefundSeeder::class,
            CustomerSeeder::class,
            CustomerAddressSeeder::class,
        ]);
    }
}
