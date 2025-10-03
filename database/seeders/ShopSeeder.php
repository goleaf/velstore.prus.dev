<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::query()->get();

        if ($vendors->isEmpty()) {
            $vendors = Vendor::factory()->count(3)->create();
        }

        foreach ($vendors as $vendor) {
            if (! Shop::query()->where('vendor_id', $vendor->id)->exists()) {
                Shop::factory()->create([
                    'vendor_id' => $vendor->id,
                    'status' => 'active',
                ]);
            }
        }

        $minimumShops = 6;
        $currentCount = Shop::query()->count();

        if ($currentCount < $minimumShops) {
            Shop::factory()->count($minimumShops - $currentCount)->create([
                'status' => 'active',
            ]);
        }
    }
}
