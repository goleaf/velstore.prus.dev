<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Refund;
use App\Models\User;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\RefundSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowcaseOrderSeedsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_seeder_creates_showcase_order_with_line_items(): void
    {
        $this->seedProducts();

        $this->seed(OrderSeeder::class);
        $this->seed(OrderSeeder::class);

        $orders = Order::orderBy('id')->get();
        $this->assertCount(3, $orders);
        $this->assertSame([1, 2, 3], $orders->pluck('id')->all());

        $showcaseOrder = $orders->last();
        $this->assertSame('showcase-order@example.com', $showcaseOrder->guest_email);
        $this->assertSame('processing', $showcaseOrder->status);
        $this->assertEquals(180.75, (float) $showcaseOrder->total_amount);

        $productIds = DB::table('products')->pluck('id')->take(2);

        $lineItems = OrderDetail::where('order_id', $showcaseOrder->id)->get();
        $this->assertCount(2, $lineItems);

        $firstItem = $lineItems->firstWhere('product_id', $productIds[0]);
        $this->assertNotNull($firstItem);
        $this->assertSame(1, $firstItem->quantity);
        $this->assertEquals(120.75, (float) $firstItem->price);

        $secondItem = $lineItems->firstWhere('product_id', $productIds[1]);
        $this->assertNotNull($secondItem);
        $this->assertSame(1, $secondItem->quantity);
        $this->assertEquals(60.00, (float) $secondItem->price);

        $this->assertSame(5, OrderDetail::count());
    }

    public function test_payment_seeder_populates_demo_payments_for_showcase_order(): void
    {
        $this->seedProducts();
        $this->seed(OrderSeeder::class);

        $this->seed(PaymentSeeder::class);
        $this->seed(PaymentSeeder::class);

        $showcaseOrder = Order::findOrFail(3);

        $payments = Payment::where('order_id', $showcaseOrder->id)
            ->orderBy('transaction_id')
            ->get();

        $this->assertCount(2, $payments);

        $stripePayment = $payments->firstWhere('transaction_id', 'STRIPE-ORDER-0003');
        $this->assertNotNull($stripePayment);
        $this->assertSame('completed', $stripePayment->status);
        $this->assertEquals(120.75, (float) $stripePayment->amount);
        $this->assertSame('stripe', $stripePayment->gateway->code);
        $this->assertSame([
            'message' => 'Payment captured successfully',
            'authorization_code' => 'AUTH-STRIPE-12075',
        ], $stripePayment->response);
        $this->assertSame([
            'ip' => '203.0.113.5',
            'captured_via' => 'dashboard',
        ], $stripePayment->meta);

        $paypalPayment = $payments->firstWhere('transaction_id', 'PAYPAL-ORDER-0003');
        $this->assertNotNull($paypalPayment);
        $this->assertSame('pending', $paypalPayment->status);
        $this->assertEquals(60.00, (float) $paypalPayment->amount);
        $this->assertSame('paypal', $paypalPayment->gateway->code);
        $this->assertSame([
            'message' => 'Awaiting capture from PayPal',
            'status_detail' => 'Pending seller review',
        ], $paypalPayment->response);
        $this->assertSame([
            'ip' => '198.51.100.42',
            'initiated_via' => 'checkout',
        ], $paypalPayment->meta);

        $this->assertTrue(User::whereEmail('testuser@example.com')->exists());
        $this->assertEqualsCanonicalizing(
            ['stripe', 'paypal'],
            PaymentGateway::pluck('code')->all()
        );
    }

    public function test_refund_seeder_creates_demo_refunds_for_completed_payment(): void
    {
        $this->seedProducts();
        $this->seed(OrderSeeder::class);
        $this->seed(PaymentSeeder::class);

        $this->seed(RefundSeeder::class);
        $this->seed(RefundSeeder::class);

        $payment = Payment::where('order_id', 3)
            ->where('status', 'completed')
            ->firstOrFail();

        $refunds = Refund::where('payment_id', $payment->id)
            ->orderBy('refund_id')
            ->get();

        $this->assertCount(2, $refunds);

        $firstRefund = $refunds->firstWhere('refund_id', 'RFND-0003-A');
        $this->assertNotNull($firstRefund);
        $this->assertEquals(45.00, (float) $firstRefund->amount);
        $this->assertSame('approved', $firstRefund->status);
        $this->assertSame('Returned accessory item', $firstRefund->reason);

        $secondRefund = $refunds->firstWhere('refund_id', 'RFND-0003-B');
        $this->assertNotNull($secondRefund);
        $this->assertEquals(15.75, (float) $secondRefund->amount);
        $this->assertSame('completed', $secondRefund->status);
        $this->assertSame('Shipping delay adjustment', $secondRefund->reason);
    }

    private function seedProducts(): void
    {
        \App\Models\Product::factory()->count(3)->create();
    }
}
