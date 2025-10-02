<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $productIds = DB::table('products')->pluck('id')->take(2)->values();
        if ($productIds->count() < 2) {
            return;  // No products available; skip seeding orders to avoid FK errors
        }

        $order1Id = DB::table('orders')->insertGetId([
            'customer_id' => null,
            'guest_email' => 'guest1@example.com',
            'total_amount' => 300,
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order2Id = DB::table('orders')->insertGetId([
            'customer_id' => null,
            'guest_email' => 'guest2@example.com',
            'total_amount' => 150,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_details')->insert([
            [
                'order_id' => $order1Id,
                'product_id' => $productIds[0],
                'quantity' => 2,
                'price' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order1Id,
                'product_id' => $productIds[1],
                'quantity' => 1,
                'price' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'order_id' => $order2Id,
                'product_id' => $productIds[0],
                'quantity' => 3,
                'price' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
