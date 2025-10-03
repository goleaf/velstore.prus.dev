<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Database\Seeder;

class CustomerShopSeeder extends Seeder
{
    public function run(): void
    {
        $shops = Shop::all();

        if ($shops->isEmpty()) {
            return;
        }

        Customer::query()->each(function (Customer $customer) use ($shops): void {
            if ($customer->shops()->exists()) {
                return;
            }

            $maxAssignable = min(3, $shops->count());
            $count = max(1, random_int(1, $maxAssignable));
            $selectedShopIds = $shops
                ->random($count)
                ->pluck('id')
                ->all();

            $customer->shops()->sync($selectedShopIds);
        });
    }
}
