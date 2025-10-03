<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = DB::table('products')->pluck('id')->take(3)->values();

        if ($productIds->count() < 2) {
            return; // Not enough products available; skip to avoid FK errors
        }

        $now = now();

        $orders = [
            1 => [
                'guest_email' => 'guest1@example.com',
                'total_amount' => 300,
                'status' => 'completed',
                'created_at' => $now->copy()->subDays(5),
            ],
            2 => [
                'guest_email' => 'guest2@example.com',
                'total_amount' => 150,
                'status' => 'pending',
                'created_at' => $now->copy()->subDays(3),
            ],
            3 => [
                'guest_email' => 'showcase-order@example.com',
                'total_amount' => 180.75,
                'status' => 'processing',
                'created_at' => $now->copy()->subDay(),
            ],
        ];

        foreach ($orders as $id => $data) {
            DB::table('orders')->updateOrInsert(
                ['id' => $id],
                [
                    'customer_id' => null,
                    'guest_email' => $data['guest_email'],
                    'total_amount' => $data['total_amount'],
                    'status' => $data['status'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $now,
                ]
            );
        }

        $details = [
            [
                'order_id' => 1,
                'product_id' => $productIds[0],
                'quantity' => 2,
                'price' => 100,
            ],
            [
                'order_id' => 1,
                'product_id' => $productIds[1] ?? $productIds[0],
                'quantity' => 1,
                'price' => 100,
            ],
            [
                'order_id' => 2,
                'product_id' => $productIds[0],
                'quantity' => 3,
                'price' => 50,
            ],
            [
                'order_id' => 3,
                'product_id' => $productIds[0],
                'quantity' => 1,
                'price' => 120.75,
            ],
            [
                'order_id' => 3,
                'product_id' => $productIds[1] ?? $productIds[0],
                'quantity' => 1,
                'price' => 60.00,
            ],
        ];

        foreach ($details as $detail) {
            DB::table('order_details')->updateOrInsert(
                [
                    'order_id' => $detail['order_id'],
                    'product_id' => $detail['product_id'],
                ],
                [
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $shippingAddresses = [
            1 => [
                'name' => 'Guest One',
                'phone' => '+1-202-555-0101',
                'address' => '123 Demo Street',
                'city' => 'Demo City',
                'postal_code' => '10001',
                'country' => 'United States',
            ],
            2 => [
                'name' => 'Guest Two',
                'phone' => '+1-202-555-0102',
                'address' => '456 Sample Avenue',
                'city' => 'Sampletown',
                'postal_code' => '30301',
                'country' => 'United States',
            ],
            3 => [
                'name' => 'Showcase Customer',
                'phone' => '+1-202-555-0199',
                'address' => '789 Showcase Boulevard',
                'city' => 'Showcase City',
                'postal_code' => '94105',
                'country' => 'United States',
            ],
        ];

        foreach ($shippingAddresses as $orderId => $address) {
            DB::table('shipping_addresses')->updateOrInsert(
                ['order_id' => $orderId],
                array_merge($address, [
                    'customer_id' => null,
                    'order_id' => $orderId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
