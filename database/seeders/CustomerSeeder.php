<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => 'password123',
                'phone' => '+1 555 0100',
                'address' => '123 Main St, Springfield',
                'status' => 'active',
                'marketing_opt_in' => true,
                'loyalty_tier' => 'gold',
                'notes' => 'Frequent shopper who prefers express shipping.',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => 'password123',
                'phone' => '+1 555 0101',
                'address' => '456 Oak Ave, Metropolis',
                'status' => 'active',
                'marketing_opt_in' => false,
                'loyalty_tier' => 'silver',
                'notes' => 'Subscribed to SMS reminders only.',
            ],
            [
                'name' => 'Alex Johnson',
                'email' => 'alex.johnson@example.com',
                'password' => 'password123',
                'phone' => '+1 555 0102',
                'address' => '789 Pine Rd, Gotham',
                'status' => 'inactive',
                'marketing_opt_in' => false,
                'loyalty_tier' => 'bronze',
                'notes' => 'Account paused after request.',
            ],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'status' => $data['status'],
                    'marketing_opt_in' => $data['marketing_opt_in'],
                    'loyalty_tier' => $data['loyalty_tier'],
                    'notes' => $data['notes'],
                ]
            );
        }

        $customersCollection = Customer::factory()->count(12)->create();
        $inactiveCustomers = Customer::factory()->inactive()->count(4)->create();

        $allCustomers = Customer::whereIn('email', collect($customers)->pluck('email'))
            ->get()
            ->merge($customersCollection)
            ->merge($inactiveCustomers);

        $shopIds = Shop::pluck('id');

        if ($shopIds->isNotEmpty()) {
            $allCustomers->each(function (Customer $customer) use ($shopIds): void {
                if ($customer->shops()->exists()) {
                    return;
                }

                $count = min(3, $shopIds->count());
                $selected = $shopIds->random($count === 1 ? 1 : random_int(1, $count));
                $customer->shops()->sync(
                    collect($selected)
                        ->unique()
                        ->values()
                        ->all()
                );
            });
        }
    }
}
