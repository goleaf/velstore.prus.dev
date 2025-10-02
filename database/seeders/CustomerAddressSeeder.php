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
            if ($customer->addresses()->exists()) {
                return;
            }

            $addresses = CustomerAddress::factory()
                ->count(3)
                ->create(['customer_id' => $customer->id]);

            $first = $addresses->first();
            if ($first) {
                $first->update(['is_default' => true]);
            }
        });
    }
}
