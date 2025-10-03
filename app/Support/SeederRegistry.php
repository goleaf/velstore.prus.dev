<?php

namespace App\Support;

class SeederRegistry
{
    /**
     * Base seeders required for the application demo data.
     *
     * @return array<string, string>
     */
    public static function baseSeeders(): array
    {
        return [
            \Database\Seeders\LanguageSeeder::class => 'Languages',
            \Database\Seeders\ProductVariantLocaleSeeder::class => 'Product variant locales',
            \Database\Seeders\CurrencySeeder::class => 'Currencies',
            \Database\Seeders\AttributeSeeder::class => 'Core attributes',
            \Database\Seeders\BrandSeeder::class => 'Brands',
            \Database\Seeders\CategorySeeder::class => 'Categories',
            \Database\Seeders\MenuSeeder::class => 'Menus',
            \Database\Seeders\BannerSeeder::class => 'Banners',
            \Database\Seeders\PageSeeder::class => 'Pages',
            \Database\Seeders\SiteSettingsSeeder::class => 'Site settings',
            \Database\Seeders\PaymentGatewaySeeder::class => 'Payment gateways',
            \Database\Seeders\PaymentGatewayConfigSeeder::class => 'Payment gateway configurations',
            \Database\Seeders\VendorSeeder::class => 'Vendors',
            \Database\Seeders\ProductSeeder::class => 'Products',
            \Database\Seeders\ProductVariantDemoSeeder::class => 'Product variants',
            \Database\Seeders\CouponSeeder::class => 'Coupons',
            \Database\Seeders\CustomerSeeder::class => 'Customers',
            \Database\Seeders\CustomerAddressSeeder::class => 'Customer addresses',
            \Database\Seeders\OrderSeeder::class => 'Sample orders',
            \Database\Seeders\CustomerOrderSeeder::class => 'Customer order history',
            \Database\Seeders\PaymentSeeder::class => 'Payments',
            \Database\Seeders\RefundSeeder::class => 'Refunds',
            \Database\Seeders\ProductReviewSeeder::class => 'Product reviews',
        ];
    }
}
