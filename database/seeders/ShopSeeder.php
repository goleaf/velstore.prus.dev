<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}
