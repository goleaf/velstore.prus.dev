<?php

namespace Database\Seeders;

use App\Models\OrderNote;
use App\Models\OrderStatusUpdate;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
                'status' => 'completed',
                'created_at' => $now->copy()->subDays(5),
                'currency' => 'USD',
                'shipping_method' => 'Standard Shipping',
                'shipping_tracking_number' => 'TRK-GUEST-0001',
                'shipping_estimated_at' => $now->copy()->subDays(1),
                'shipping_amount' => 12.50,
                'discount_amount' => 10.00,
                'tax_amount' => 14.25,
                'adjustment_amount' => 0.00,
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
                'status' => 'pending',
                'created_at' => $now->copy()->subDays(3),
                'currency' => 'USD',
                'shipping_method' => 'Economy Parcel',
                'shipping_tracking_number' => 'TRK-GUEST-0002',
                'shipping_estimated_at' => $now->copy()->addDays(2),
                'shipping_amount' => 8.75,
                'discount_amount' => 0.00,
                'tax_amount' => 9.10,
                'adjustment_amount' => 0.00,
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
                'status' => 'processing',
                'created_at' => $now->copy()->subDay(),
                'currency' => 'USD',
                'shipping_method' => 'Express Courier',
                'shipping_tracking_number' => 'TRK-SHOW-0003',
                'shipping_estimated_at' => $now->copy()->addDay(),
                'shipping_amount' => 15.00,
                'discount_amount' => 5.25,
                'tax_amount' => 12.00,
                'adjustment_amount' => 0.00,
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

        if ($productIds->count() >= 1) {
            $orders[4] = [
                'guest_email' => 'cancelled-order@example.com',
                'status' => 'canceled',
                'created_at' => $now->copy()->subHours(12),
                'items' => [
                    ['product_id' => $productIds[0], 'quantity' => 1, 'price' => 45.5],
                ],
                'shipping' => [
                    'name' => 'Guest Three',
                    'phone' => '+1-202-555-0103',
                    'address' => '321 Sample Parkway',
                    'city' => 'Reference City',
                    'postal_code' => '73301',
                    'country' => 'United States',
                ],
            ];
        }

        $orders[29] = [
            'guest_email' => 'vip-shopper@example.com',
            'status' => 'processing',
            'created_at' => $now->copy()->subHours(10),
            'currency' => 'USD',
            'shipping_method' => 'Same Day Priority',
            'shipping_tracking_number' => 'TRK-VIP-0029',
            'shipping_estimated_at' => $now->copy()->addDay(),
            'shipping_amount' => 24.95,
            'discount_amount' => 20.00,
            'tax_amount' => 18.30,
            'adjustment_amount' => -5.00,
            'items' => [
                ['product_id' => $productIds[0], 'quantity' => 2, 'price' => 150.00],
                ['product_id' => $productIds[1], 'quantity' => 1, 'price' => 95.50],
                ['product_id' => $productIds[2] ?? $productIds[0], 'quantity' => 1, 'price' => 45.00],
            ],
            'shipping' => [
                'name' => 'Priority Customer',
                'phone' => '+1-202-555-0158',
                'address' => '456 Priority Lane',
                'city' => 'Velocity City',
                'postal_code' => '73301',
                'country' => 'United States',
            ],
            'status_updates' => [
                [
                    'status' => 'pending',
                    'label' => 'Order Placed',
                    'description' => 'Customer completed checkout using express method.',
                    'happened_at' => $now->copy()->subHours(10),
                ],
                [
                    'status' => 'processing',
                    'label' => 'Payment Captured',
                    'description' => 'Stripe payment confirmed and funds captured.',
                    'happened_at' => $now->copy()->subHours(9)->subMinutes(20),
                ],
                [
                    'status' => 'processing',
                    'label' => 'Items Packed',
                    'description' => 'Warehouse prepared the order with fragile handling.',
                    'happened_at' => $now->copy()->subHours(6),
                ],
                [
                    'status' => 'processing',
                    'label' => 'Courier Picked Up',
                    'description' => 'Handed to Same Day Priority courier for delivery.',
                    'happened_at' => $now->copy()->subHours(2),
                ],
            ],
            'notes' => [
                [
                    'author' => 'Avery Harper',
                    'note' => 'Customer requested premium gift wrap and handwritten card.',
                    'is_internal' => true,
                    'created_at' => $now->copy()->subHours(8),
                ],
                [
                    'author' => 'Automation Bot',
                    'note' => 'Tracking link and SMS updates sent to customer.',
                    'is_internal' => false,
                    'created_at' => $now->copy()->subHours(1),
                ],
            ],
        ];

        foreach ($orders as $id => $payload) {
            $items = collect($payload['items'] ?? []);
            $itemsTotal = $items->reduce(
                static fn (float $carry, array $item): float => $carry + ($item['quantity'] * $item['price']),
                0.0
            );
            $shippingAmount = (float) ($payload['shipping_amount'] ?? 0);
            $discountAmount = (float) ($payload['discount_amount'] ?? 0);
            $taxAmount = (float) ($payload['tax_amount'] ?? 0);
            $adjustmentAmount = (float) ($payload['adjustment_amount'] ?? 0);
            $total = $itemsTotal + $shippingAmount - $discountAmount + $taxAmount + $adjustmentAmount;
            $createdAt = $payload['created_at'] ?? $now;
            $estimatedAt = $payload['shipping_estimated_at'] ?? null;

            DB::table('orders')->updateOrInsert(
                ['id' => $id],
                [
                    'customer_id' => null,
                    'guest_email' => $payload['guest_email'],
                    'total_amount' => number_format($total, 2, '.', ''),
                    'currency' => $payload['currency'] ?? 'USD',
                    'shipping_method' => $payload['shipping_method'] ?? null,
                    'shipping_tracking_number' => $payload['shipping_tracking_number'] ?? null,
                    'shipping_estimated_at' => $estimatedAt,
                    'shipping_amount' => number_format($shippingAmount, 2, '.', ''),
                    'discount_amount' => number_format($discountAmount, 2, '.', ''),
                    'tax_amount' => number_format($taxAmount, 2, '.', ''),
                    'adjustment_amount' => number_format($adjustmentAmount, 2, '.', ''),
                    'status' => $payload['status'],
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ]
            );

            foreach ($items as $item) {
                DB::table('order_details')->updateOrInsert(
                    [
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'created_at' => $createdAt,
                        'updated_at' => $now,
                    ]
                );
            }

            if (! empty($payload['shipping'])) {
                ShippingAddress::updateOrCreate(
                    ['order_id' => $id],
                    array_merge(
                        ['customer_id' => null],
                        $payload['shipping']
                    )
                );
            }

            $statusUpdates = collect($payload['status_updates'] ?? []);
            if ($statusUpdates->isNotEmpty()) {
                OrderStatusUpdate::where('order_id', $id)->delete();

                foreach ($statusUpdates as $update) {
                    $happenedAt = $update['happened_at'] ?? $createdAt;

                    $statusUpdate = new OrderStatusUpdate([
                        'order_id' => $id,
                        'status' => $update['status'],
                        'label' => $update['label'] ?? Str::headline($update['status']),
                        'description' => $update['description'] ?? null,
                        'happened_at' => $happenedAt,
                    ]);

                    $statusUpdate->created_at = $happenedAt;
                    $statusUpdate->updated_at = $happenedAt;
                    $statusUpdate->save();
                }
            }

            $notes = collect($payload['notes'] ?? []);
            if ($notes->isNotEmpty()) {
                OrderNote::where('order_id', $id)->delete();

                foreach ($notes as $note) {
                    $noteModel = new OrderNote([
                        'order_id' => $id,
                        'author_name' => $note['author'] ?? null,
                        'is_internal' => $note['is_internal'] ?? true,
                        'note' => $note['note'],
                    ]);

                    $timestamp = $note['created_at'] ?? $createdAt;
                    $noteModel->created_at = $timestamp;
                    $noteModel->updated_at = $timestamp;
                    $noteModel->save();
                }
            }
        }
    }
}
