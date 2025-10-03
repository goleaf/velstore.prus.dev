<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $productIds = Product::query()->orderBy('id')->limit(2)->pluck('id');
        if ($productIds->count() < 2) {
            return; // No products available; skip seeding orders to avoid FK errors
        }

        $orders = [
            [
                'guest_email' => 'guest1@example.com',
                'status' => 'completed',
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
            [
                'guest_email' => 'guest2@example.com',
                'status' => 'pending',
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

        foreach ($orders as $payload) {
            $total = collect($payload['items'])->reduce(
                fn (float $carry, array $item) => $carry + ($item['quantity'] * $item['price']),
                0.0
            );

            $order = Order::updateOrCreate(
                ['guest_email' => $payload['guest_email']],
                [
                    'customer_id' => null,
                    'status' => $payload['status'],
                    'total_amount' => number_format($total, 2, '.', ''),
                ]
            );

            foreach ($payload['items'] as $item) {
                OrderDetail::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]
                );
            }

            ShippingAddress::updateOrCreate(
                ['order_id' => $order->id],
                array_merge(
                    [
                        'customer_id' => null,
                    ],
                    $payload['shipping']
                )
            );
        }
    }
}
