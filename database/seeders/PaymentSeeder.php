<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\ShippingAddress;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultShopId = Shop::query()->value('id');

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

        $guestOrder = Order::updateOrCreate(
            ['guest_email' => 'guest@example.com'],
            [
                'customer_id' => null,
                'shop_id' => $defaultShopId,
                'total_amount' => 100.00,
                'status' => 'pending',
            ]
        );

        ShippingAddress::updateOrCreate(
            ['order_id' => $guestOrder->id],
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

        Payment::updateOrCreate(
            [
                'order_id' => $guestOrder->id,
                'transaction_id' => 'GUEST-ORDER-0001',
            ],
            [
                'gateway_id' => $stripeGateway->id,
                'amount' => 100.00,
                'currency' => 'USD',
                'status' => 'completed',
                'response' => ['message' => 'Payment successful'],
                'meta' => ['ip' => '127.0.0.1', 'seeded' => true],
            ]
        );

        $showcaseOrder = Order::updateOrCreate(
            ['guest_email' => 'showcase-order@example.com'],
            [
                'customer_id' => null,
                'shop_id' => $defaultShopId,
                'total_amount' => 180.75,
                'status' => 'processing',
            ]
        );

        ShippingAddress::updateOrCreate(
            ['order_id' => $showcaseOrder->id],
            [
                'customer_id' => null,
                'name' => 'Showcase Customer',
                'phone' => '+1-202-555-0199',
                'address' => '789 Showcase Boulevard',
                'city' => 'Showcase City',
                'postal_code' => '94105',
                'country' => 'United States',
            ]
        );

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
                    'order_id' => $showcaseOrder->id,
                    'transaction_id' => $data['transaction_id'],
                ],
                [
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
