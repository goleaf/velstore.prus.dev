<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $productIds = Product::query()->orderBy('id')->limit(3)->pluck('id');

        if ($productIds->count() < 2) {
            return; // Not enough products; skip to avoid FK issues
        }

        $now = now();

        $orders = [
            1 => [
                'guest_email' => 'guest1@example.com',
                'total_amount' => 300,
                'status' => 'completed',
                'created_at' => $now->copy()->subDays(5),
                'items' => [
                    ['product_id' => $productIds[0], 'quantity' => 2, 'price' => 100],
                    ['product_id' => $productIds[1], 'quantity' => 1, 'price' => 100],
                ],
                'shipping' => [
                    'name' => 'Guest One',
                    'phone' => '+1-202-555-0101',
                    'address' => '123 Demo Street',
                    'city' => 'Demo City',
                    'postal_code' => '10001',
                    'country' => 'United States',
                ],
            ],
            2 => [
                'guest_email' => 'guest2@example.com',
                'total_amount' => 150,
                'status' => 'pending',
                'created_at' => $now->copy()->subDays(3),
                'items' => [
                    ['product_id' => $productIds[0], 'quantity' => 3, 'price' => 50],
                ],
                'shipping' => [
                    'name' => 'Guest Two',
                    'phone' => '+1-202-555-0102',
                    'address' => '456 Sample Avenue',
                    'city' => 'Sampletown',
                    'postal_code' => '30301',
                    'country' => 'United States',
                ],
            ],
        ];

        if ($productIds->count() >= 2) {
            $orders[3] = [
                'guest_email' => 'showcase-order@example.com',
                'total_amount' => 180.75,
                'status' => 'processing',
                'created_at' => $now->copy()->subDay(),
                'items' => [
                    ['product_id' => $productIds[0], 'quantity' => 1, 'price' => 120.75],
                    ['product_id' => $productIds[1], 'quantity' => 1, 'price' => 60.00],
                ],
                'shipping' => [
                    'name' => 'Showcase Customer',
                    'phone' => '+1-202-555-0199',
                    'address' => '789 Showcase Boulevard',
                    'city' => 'Showcase City',
                    'postal_code' => '94105',
                    'country' => 'United States',
                ],
            ];
        }

        foreach ($orders as $id => $payload) {
            DB::table('orders')->updateOrInsert(
                ['id' => $id],
                [
                    'customer_id' => null,
                    'guest_email' => $payload['guest_email'],
                    'total_amount' => $payload['total_amount'],
                    'status' => $payload['status'],
                    'created_at' => $payload['created_at'],
                    'updated_at' => $now,
                ]
            );

            foreach ($payload['items'] as $item) {
                DB::table('order_details')->updateOrInsert(
                    [
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            if (isset($payload['shipping'])) {
                ShippingAddress::updateOrCreate(
                    ['order_id' => $id],
                    array_merge(
                        ['customer_id' => null],
                        $payload['shipping']
                    )
                );
            }
        }
    }
}
