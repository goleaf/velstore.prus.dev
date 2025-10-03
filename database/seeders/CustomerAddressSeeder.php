<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerAddressSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->each(function (Customer $customer): void {
            $existingCount = $customer->addresses()->count();
            $targetCount = 3;

            if ($existingCount < $targetCount) {
                $newAddresses = CustomerAddress::factory()
                    ->count($targetCount - $existingCount)
                    ->create(['customer_id' => $customer->id]);

                if ($existingCount === 0 && $newAddresses->isNotEmpty()) {
                    $newAddresses->first()->update(['is_default' => true]);
                }
            }

            $customer->ensureDefaultAddress();
        });
    }
}
