<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::take(10)->get();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($customers, $products) {
            foreach ($customers as $customer) {
                // Create one order per status per customer to cover all states
                foreach (['pending', 'processing', 'completed', 'canceled'] as $status) {
                    // Idempotency: if an order for this customer+status exists, skip creating another
                    $existing = Order::where('customer_id', $customer->id)
                        ->where('status', $status)
                        ->first();

                    if ($existing) {
                        continue;
                    }

                    $order = Order::create([
                        'customer_id' => $customer->id,
                        'guest_email' => null,
                        'total_amount' => 0,
                        'status' => $status,
                    ]);

                    $lineCount = rand(2, 4);
                    $picked = $products->shuffle()->take($lineCount);
                    $total = 0;

                    foreach ($picked as $product) {
                        $qty = rand(1, 3);
                        $price = (float) ($product->price ?? 29.99);
                        $lineTotal = $qty * $price;

                        OrderDetail::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'price' => $price,
                        ]);

                        $total += $lineTotal;
                    }

                    $order->update(['total_amount' => number_format($total, 2, '.', '')]);
                }
            }
        });
    }
}
