<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
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

        $paypalGateway = PaymentGateway::firstOrCreate(
            ['code' => 'paypal'],
            [
                'name' => 'PayPal',
                'description' => 'PayPal payment gateway',
                'is_active' => true,
            ]
        );

        $order = Order::find(3);

        if (! $order) {
            $order = Order::create([
                'customer_id' => null,
                'guest_email' => 'showcase-order@example.com',
                'total_amount' => 180.75,
                'status' => 'processing',
            ]);
        } else {
            $order->update([
                'total_amount' => 180.75,
                'status' => 'processing',
            ]);
        }

        $payments = [
            [
                'gateway' => $stripeGateway,
                'transaction_id' => 'STRIPE-ORDER-0003',
                'amount' => 120.75,
                'status' => 'completed',
                'currency' => 'USD',
                'response' => [
                    'message' => 'Payment captured successfully',
                    'authorization_code' => 'AUTH-STRIPE-12075',
                ],
                'meta' => [
                    'ip' => '203.0.113.5',
                    'captured_via' => 'dashboard',
                ],
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'gateway' => $paypalGateway,
                'transaction_id' => 'PAYPAL-ORDER-0003',
                'amount' => 60.00,
                'status' => 'pending',
                'currency' => 'USD',
                'response' => [
                    'message' => 'Awaiting capture from PayPal',
                    'status_detail' => 'Pending seller review',
                ],
                'meta' => [
                    'ip' => '198.51.100.42',
                    'initiated_via' => 'checkout',
                ],
                'created_at' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($payments as $data) {
            $payment = Payment::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'transaction_id' => $data['transaction_id'],
                ],
                [
                    'user_id' => $user->id,
                    'gateway_id' => $data['gateway']->id,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => $data['status'],
                    'response' => $data['response'],
                    'meta' => $data['meta'],
                ]
            );

            if (isset($data['created_at'])) {
                $payment->created_at = $data['created_at'];
                $payment->updated_at = Carbon::now();
                $payment->save();
            }
        }
    }
}
