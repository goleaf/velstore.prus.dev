<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
<<<<<<< HEAD
        $vendor = Vendor::query()->orderBy('id')->first()
            ?? Vendor::factory()->create([
                'status' => 'active',
            ]);

        $shops = [
            [
                'name' => 'Urban Threads',
                'slug' => 'urban-threads',
                'description' => 'Streetwear essentials and lifestyle picks.',
            ],
            [
                'name' => 'Nordic Living',
                'slug' => 'nordic-living',
                'description' => 'Scandinavian-inspired home and decor.',
            ],
            [
                'name' => 'Pulse Electronics',
                'slug' => 'pulse-electronics',
                'description' => 'Gadgets, accessories, and smart devices.',
            ],
        ];

        foreach ($shops as $data) {
            Shop::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'vendor_id' => $vendor->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'status' => 'active',
                    'logo' => $data['logo'] ?? null,
                ]
            );
        }

        // Ensure there is always at least one default shop for products
        if (! Shop::query()->where('slug', 'default-shop')->exists()) {
            Shop::firstOrCreate(
                ['slug' => 'default-shop'],
                [
                    'vendor_id' => $vendor->id,
                    'name' => 'Default Shop',
                    'description' => 'Auto-generated default shop for catalog seeding.',
                    'status' => 'active',
                ]
            );
        }

        // Create additional shops for vendors
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
=======
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
>>>>>>> origin/codex/refactor-customer-creation-and-integrate-features
        }
    }
}
