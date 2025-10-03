<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        if (Vendor::query()->exists()) {
            return;
        }

        Vendor::factory()
            ->count(12)
            ->state(new Sequence(
                ['status' => 'active'],
                ['status' => 'inactive'],
                ['status' => 'banned'],
            ))
            ->create();
    }
}
