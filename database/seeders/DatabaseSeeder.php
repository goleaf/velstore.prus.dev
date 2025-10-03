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
            ProductSeeder::class,
            CouponSeeder::class,
            OrderSeeder::class,
            PaymentGatewaySeeder::class,
            PaymentSeeder::class,
            RefundSeeder::class,
            CustomerSeeder::class,
            CustomerAddressSeeder::class,
            ProductVariantDemoSeeder::class,
        ]);
    }
}
