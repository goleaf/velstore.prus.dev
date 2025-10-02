<?php

namespace Database\Seeders;

use App\Models\Customer;
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
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => 'password123',
                'phone' => '+1 555 0101',
                'address' => '456 Oak Ave, Metropolis',
                'status' => 'active',
            ],
            [
                'name' => 'Alex Johnson',
                'email' => 'alex.johnson@example.com',
                'password' => 'password123',
                'phone' => '+1 555 0102',
                'address' => '789 Pine Rd, Gotham',
                'status' => 'inactive',
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
                ]
            );
        }
    }
}
