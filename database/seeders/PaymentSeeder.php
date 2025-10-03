<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $gateways = $this->ensureGateways();

            $this->seedFeaturedPayments($gateways);
            $this->seedShopPayments($gateways);
        });
    }

    protected function ensureGateways(): Collection
    {
        $gatewayDefinitions = collect([
            [
                'code' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Stripe payment gateway',
            ],
            [
                'code' => 'paypal',
                'name' => 'PayPal',
                'description' => 'PayPal payment gateway',
            ],
        ]);

        return $gatewayDefinitions->map(function (array $definition) {
            return PaymentGateway::firstOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_active' => true,
                ]
            );
        });
    }

    protected function seedFeaturedPayments(Collection $gateways): void
    {
        $products = Product::query()->with('shop')->orderBy('id')->take(3)->get();

        if ($products->isEmpty()) {
            return;
        }

        $stripeGateway = $gateways->firstWhere('code', 'stripe') ?? $gateways->first();
        $paypalGateway = $gateways->firstWhere('code', 'paypal') ?? $gateways->first();

        $guestOrderItems = [
            [
                'product' => $products[0],
                'quantity' => 2,
                'price' => $products[0]->price ?: 50.00,
            ],
        ];

        $guestOrder = $this->upsertOrder('guest@example.com', 'pending', $guestOrderItems, Carbon::now()->subDays(7));
        $this->ensureShippingAddress($guestOrder, [
            'name' => 'Guest Checkout',
            'phone' => '+1-202-555-0199',
            'address' => '789 Example Road',
            'city' => 'Seedville',
            'postal_code' => '60601',
            'country' => 'United States',
        ]);

        $guestMeta = array_merge(
            [
                'seeded' => true,
                'ip' => '127.0.0.1',
                'source' => 'guest-demo',
            ],
            $this->shopMetaFromOrder($guestOrder)
        );

        Payment::updateOrCreate(
            [
                'order_id' => $guestOrder->id,
                'transaction_id' => 'GUEST-ORDER-0001',
            ],
            [
                'gateway_id' => $stripeGateway->id,
                'amount' => $guestOrder->total_amount,
                'currency' => $products[0]->currency ?? 'USD',
                'status' => 'completed',
                'response' => ['message' => 'Payment successful'],
                'meta' => $guestMeta,
            ]
        );

        $showcaseItems = [
            [
<<<<<<< HEAD
                'product' => $products[0],
                'quantity' => 1,
                'price' => $products[0]->price ?: 120.75,
            ],
            [
                'product' => $products[1] ?? $products[0],
                'quantity' => 1,
                'price' => $products[1]->price ?? 60.00,
            ],
        ];
=======
                'customer_id' => null,
                'shop_id' => $defaultShopId,
                'total_amount' => 180.75,
                'status' => 'processing',
            ]
        );
>>>>>>> origin/codex/refactor-admin-orders-and-add-features

        $showcaseOrder = $this->upsertOrder('showcase-order@example.com', 'processing', $showcaseItems, Carbon::now()->subDays(2));
        $this->ensureShippingAddress($showcaseOrder, [
            'name' => 'Showcase Customer',
            'phone' => '+1-202-555-0199',
            'address' => '789 Showcase Boulevard',
            'city' => 'Showcase City',
            'postal_code' => '94105',
            'country' => 'United States',
        ]);

        $showcasePayments = [
            [
                'transaction_id' => 'STRIPE-ORDER-0003',
                'gateway' => $stripeGateway,
                'status' => 'completed',
                'amount' => 120.75,
                'created_at' => Carbon::now()->subDays(2),
                'response' => [
                    'message' => 'Payment captured successfully',
                    'authorization_code' => 'AUTH-STRIPE-12075',
                ],
                'meta' => [
                    'ip' => '203.0.113.5',
                    'captured_via' => 'dashboard',
                ],
            ],
            [
                'transaction_id' => 'PAYPAL-ORDER-0003',
                'gateway' => $paypalGateway,
                'status' => 'pending',
                'amount' => 60.00,
                'created_at' => Carbon::now()->subDay(),
                'response' => [
                    'message' => 'Awaiting capture from PayPal',
                    'status_detail' => 'Pending seller review',
                ],
                'meta' => [
                    'ip' => '198.51.100.42',
                    'initiated_via' => 'checkout',
                ],
            ],
        ];

        foreach ($showcasePayments as $payload) {
            $payment = Payment::updateOrCreate(
                [
                    'order_id' => $showcaseOrder->id,
                    'transaction_id' => $payload['transaction_id'],
                ],
                [
                    'gateway_id' => $payload['gateway']->id,
                    'amount' => number_format($payload['amount'] ?? $showcaseOrder->total_amount, 2, '.', ''),
                    'currency' => $products[0]->currency ?? 'USD',
                    'status' => $payload['status'],
                    'response' => $payload['response'],
                    'meta' => array_merge(
                        ['seeded' => true],
                        $payload['meta'],
                        $this->shopMetaFromOrder($showcaseOrder)
                    ),
                ]
            );

            if (isset($payload['created_at'])) {
                $payment->created_at = $payload['created_at'];
                $payment->updated_at = $payload['created_at'];
                $payment->save();
            }
        }
    }

    protected function seedShopPayments(Collection $gateways): void
    {
        $shops = Shop::with(['products' => fn ($query) => $query->orderBy('id')])->get();

        if ($shops->isEmpty()) {
            return;
        }

        $now = Carbon::now();

        foreach ($shops as $index => $shop) {
            $product = $shop->products->first();

            if (! $product) {
                continue;
            }

            $scenarios = [
                [
                    'status' => 'completed',
                    'order_status' => 'completed',
                    'suffix' => 'completed',
                    'amount' => (float) $product->price ?: 75.00,
                    'created_at' => $now->copy()->subDays(min($index, 6)),
                ],
                [
                    'status' => 'pending',
                    'order_status' => 'processing',
                    'suffix' => 'pending',
                    'amount' => max((float) $product->price * 0.65, 25.00),
                    'created_at' => $now->copy()->subDays(min($index + 1, 8)),
                ],
            ];

            foreach ($scenarios as $scenario) {
                $email = sprintf('%s-%s@velstore.test', Str::slug($shop->name), $scenario['suffix']);

                $order = $this->upsertOrder($email, $scenario['order_status'], [
                    [
                        'product' => $product,
                        'quantity' => 1,
                        'price' => $scenario['amount'],
                    ],
                ], $scenario['created_at']);

                $this->ensureShippingAddress($order, [
                    'name' => $shop->name,
                    'phone' => '+1-202-555-0101',
                    'address' => '123 Market Street',
                    'city' => 'Commerce City',
                    'postal_code' => '10001',
                    'country' => 'United States',
                ]);

                $gateway = $gateways->random();

                $payment = Payment::updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'transaction_id' => strtoupper(Str::slug($shop->name, '_')) . '-' . strtoupper($scenario['status']),
                    ],
                    [
                        'gateway_id' => $gateway->id,
                        'amount' => number_format($scenario['amount'], 2, '.', ''),
                        'currency' => $product->currency ?? 'USD',
                        'status' => $scenario['status'],
                        'response' => [
                            'message' => 'Seeded payment for ' . $shop->name,
                            'gateway' => $gateway->code,
                        ],
                        'meta' => array_merge(
                            [
                                'seeded' => true,
                                'shop_id' => $shop->id,
                                'shop_name' => $shop->name,
                                'vendor_id' => $shop->vendor_id,
                            ],
                            $this->shopMetaFromOrder($order)
                        ),
                    ]
                );

                if ($scenario['status'] === 'completed') {
                    $payment->created_at = $scenario['created_at'];
                    $payment->updated_at = $scenario['created_at'];
                    $payment->save();
                }
            }
        }
    }

    protected function upsertOrder(string $guestEmail, string $status, array $items, ?Carbon $timestamp = null): Order
    {
        $order = Order::firstOrNew(['guest_email' => $guestEmail]);
        $order->customer_id = $order->customer_id ?? null;
        $order->status = $status;
        $order->save();

        $this->syncOrderItems($order, $items);

        if ($timestamp) {
            $order->created_at = $timestamp;
            $order->updated_at = $timestamp;
            $order->save();
        }

        return $order;
    }

    protected function syncOrderItems(Order $order, array $items): void
    {
        $total = 0.0;

        foreach ($items as $item) {
            $product = $item['product'] instanceof Product ? $item['product'] : Product::find($item['product']);

            if (! $product) {
                continue;
            }

            $quantity = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? $product->price ?? 0.0);
            $total += $quantity * $price;

            OrderDetail::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => $quantity,
                    'price' => $price,
                ]
            );
        }

        if ($total > 0) {
            $shippingAmount = (float) ($order->shipping_amount ?? 0);
            $discountAmount = (float) ($order->discount_amount ?? 0);
            $taxAmount = (float) ($order->tax_amount ?? 0);
            $adjustmentAmount = (float) ($order->adjustment_amount ?? 0);

            $order->total_amount = number_format(
                $total + $shippingAmount - $discountAmount + $taxAmount + $adjustmentAmount,
                2,
                '.',
                ''
            );
            $order->save();
        }
    }

    protected function ensureShippingAddress(Order $order, array $attributes): void
    {
        ShippingAddress::updateOrCreate(
            ['order_id' => $order->id],
            array_merge(['customer_id' => null], $attributes)
        );
    }

    protected function shopMetaFromOrder(Order $order): array
    {
        $shops = $order->details()
            ->with('product.shop')
            ->get()
            ->map(fn ($detail) => $detail->product?->shop)
            ->filter();

        if ($shops->isEmpty()) {
            return [];
        }

        return [
            'shop_ids' => $shops->pluck('id')->unique()->values()->all(),
            'shop_names' => $shops->pluck('name')->unique()->values()->all(),
        ];
    }
}
