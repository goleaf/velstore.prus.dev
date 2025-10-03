<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        if (Shop::query()->exists()) {
            return;
        }

        $vendors = Vendor::all();

        if ($vendors->isEmpty()) {
            $vendors = Vendor::factory()->count(3)->create([
                'status' => 'active',
            ]);
        }

        foreach ($vendors as $vendor) {
            Shop::factory()
                ->count(2)
                ->create([
                    'vendor_id' => $vendor->id,
                    'status' => 'active',
                ]);
        }
    }
}
