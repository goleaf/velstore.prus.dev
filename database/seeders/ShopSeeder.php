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

        $vendors = Vendor::query()->get();

        if ($vendors->isEmpty()) {
            $vendors = Vendor::factory()->count(3)->create();
        }

        foreach ($vendors as $vendor) {
            Shop::factory()->create([
                'vendor_id' => $vendor->id,
                'status' => $vendor->status === 'banned' ? 'inactive' : 'active',
            ]);
        }
    }
}
