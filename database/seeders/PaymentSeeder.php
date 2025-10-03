<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\ShippingAddress;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'testuser@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $stripeGateway = PaymentGateway::firstOrCreate(
            ['code' => 'stripe'],
            [
                'name' => 'Stripe',
                'description' => 'Stripe payment gateway',
                'is_active' => true,
            ]
        );

        $order = Order::updateOrCreate(
            ['guest_email' => 'guest@example.com'],
            [
                'customer_id' => null,
                'total_amount' => 100.00,
                'status' => 'pending',
            ]
        );

        ShippingAddress::updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => null,
                'name' => 'Guest Checkout',
                'phone' => '+1-202-555-0199',
                'address' => '789 Example Road',
                'city' => 'Seedville',
                'postal_code' => '60601',
                'country' => 'United States',
            ]
        );

        if (! $order->payments()->exists()) {
            Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'gateway_id' => $gateway->id,
                'amount' => 100.00,
                'currency' => 'USD',
                'status' => 'completed',
                'transaction_id' => (string) Str::uuid(),
                'response' => ['message' => 'Payment successful'],
                'meta' => ['ip' => '127.0.0.1'],
            ]);
        }
    }
}
