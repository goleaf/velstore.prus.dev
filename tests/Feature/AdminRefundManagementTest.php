<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRefundManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.locale' => 'en']);

        $admin = User::factory()->create();
        $this->actingAs($admin);
    }

    public function test_index_displays_filters_and_stats(): void
    {
        Refund::factory()->count(2)->create();

        $response = $this->get(route('admin.refunds.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.refunds.index')
            ->assertViewHas('stats', function ($stats) {
                return is_array($stats)
                    && array_key_exists('total', $stats)
                    && array_key_exists('completed', $stats)
                    && array_key_exists('refunded_amount', $stats);
            })
            ->assertSee(__('cms.refunds.filters_title'))
            ->assertSee(__('cms.refunds.summary_total_count'));
    }

    public function test_admin_can_filter_refunds_by_status(): void
    {
        $completedRefund = Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
        ]);
        Refund::factory()->create([
            'status' => Refund::STATUS_PENDING,
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'status' => [Refund::STATUS_COMPLETED],
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);

        $this->assertCount(1, $data);
        $this->assertStringContainsString(__('cms.refunds.status_labels.completed'), $data[0]['status']);
        $this->assertSame($completedRefund->id, (int) $data[0]['id']);
    }

    public function test_admin_can_filter_refunds_by_date_range(): void
    {
        Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        $recentRefund = Refund::factory()->create([
            'status' => Refund::STATUS_COMPLETED,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'date_from' => now()->subDays(3)->toDateString(),
                'date_to' => now()->toDateString(),
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);
        $ids = array_map(static fn ($row) => (int) $row['id'], $data);

        $this->assertContains($recentRefund->id, $ids);
    }

    public function test_admin_can_filter_refunds_by_shop(): void
    {
        $gateway = PaymentGateway::factory()->create(['code' => 'stripe']);
        $shopA = Shop::factory()->create(['name' => 'Alpha Collective']);
        $shopB = Shop::factory()->create(['name' => 'Beta Collective']);

        $refundA = $this->createRefundForShop($shopA, $gateway, [
            'refund_id' => 'REF-SHOP-A',
            'status' => Refund::STATUS_COMPLETED,
        ]);
        $this->createRefundForShop($shopB, $gateway, [
            'refund_id' => 'REF-SHOP-B',
            'status' => Refund::STATUS_PENDING,
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'shop_id' => $shopA->id,
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);

        $this->assertCount(1, $data);
        $this->assertSame('REF-SHOP-A', $data[0]['reference']);
        $this->assertSame($shopA->name, $data[0]['shop']);
    }

    public function test_admin_can_filter_refunds_by_gateway(): void
    {
        $gatewayA = PaymentGateway::factory()->create(['code' => 'stripe']);
        $gatewayB = PaymentGateway::factory()->create(['code' => 'paypal']);
        $shop = Shop::factory()->create();

        $this->createRefundForShop($shop, $gatewayA, [
            'refund_id' => 'GATEWAY-A',
            'status' => Refund::STATUS_COMPLETED,
        ]);
        $this->createRefundForShop($shop, $gatewayB, [
            'refund_id' => 'GATEWAY-B',
            'status' => Refund::STATUS_COMPLETED,
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'gateway_id' => $gatewayA->id,
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);

        $this->assertCount(1, $data);
        $this->assertSame('GATEWAY-A', $data[0]['reference']);
    }

    public function test_admin_can_search_refunds_by_reference(): void
    {
        $gateway = PaymentGateway::factory()->create(['code' => 'stripe']);
        $shop = Shop::factory()->create();

        $targetRefund = $this->createRefundForShop($shop, $gateway, [
            'refund_id' => 'SEARCH-123',
            'reason' => 'Searchable refund entry',
        ]);
        $this->createRefundForShop($shop, $gateway, [
            'refund_id' => 'OTHER-456',
        ]);

        $response = $this->getJson(
            route('admin.refunds.getData', [
                'search_term' => 'SEARCH-123',
            ]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertOk();

        $data = $response->json('data', []);

        $this->assertCount(1, $data);
        $this->assertSame($targetRefund->id, (int) $data[0]['id']);
    }

    private function createRefundForShop(Shop $shop, PaymentGateway $gateway, array $attributes = []): Refund
    {
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'vendor_id' => $shop->vendor_id,
            'seller_id' => $shop->vendor_id,
        ]);

        $order = Order::factory()->create([
            'status' => 'completed',
        ]);

        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 75.00,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'gateway_id' => $gateway->id,
            'status' => 'completed',
            'amount' => $attributes['amount'] ?? 75.00,
            'currency' => $attributes['currency'] ?? 'USD',
        ]);

        return Refund::factory()->create(array_merge([
            'payment_id' => $payment->id,
            'amount' => 35.00,
            'currency' => 'USD',
            'status' => Refund::STATUS_COMPLETED,
        ], $attributes));
    }
}
