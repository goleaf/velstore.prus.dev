<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
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

class RefundDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
    }

    public function test_index_renders_refund_dashboard_summary(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $gateway = PaymentGateway::factory()->create();
        $shop = Shop::factory()->create();
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'vendor_id' => $shop->vendor_id,
        ]);

        $customer = Customer::factory()->create();
        $order = Order::factory()->for($customer)->create();
        OrderDetail::factory()->for($order)->for($product)->create();

        $payment = Payment::factory()
            ->for($order)
            ->for($gateway)
            ->create([
                'amount' => 300,
                'currency' => 'USD',
                'status' => 'completed',
            ]);

        $highRefund = Refund::factory()->for($payment)->create([
            'amount' => 180,
            'currency' => 'USD',
            'status' => Refund::STATUS_COMPLETED,
            'refund_id' => 'TEST-REFUND-1',
        ]);
        $highRefund->updateQuietly([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDay(),
        ]);

        $lowRefund = Refund::factory()->for($payment)->create([
            'amount' => 25,
            'currency' => 'USD',
            'status' => Refund::STATUS_PENDING,
            'refund_id' => 'TEST-REFUND-2',
        ]);
        $lowRefund->updateQuietly([
            'created_at' => now()->subDay(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.refunds.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.refunds.index')
            ->assertViewHas('summary', function (array $summary) {
                $this->assertSame(2, $summary['total']);
                $this->assertSame(1, $summary['completed']);
                $this->assertSame(1, $summary['shops_impacted']);

                return true;
            })
            ->assertViewHas('statusDistribution', function ($distribution) {
                $completed = $distribution->firstWhere('status', Refund::STATUS_COMPLETED);

                return $completed && $completed['count'] === 1;
            });
    }

    public function test_amount_filters_apply_to_datatable_and_export(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $gateway = PaymentGateway::factory()->create();
        $shop = Shop::factory()->create();
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'vendor_id' => $shop->vendor_id,
        ]);

        $customer = Customer::factory()->create();
        $order = Order::factory()->for($customer)->create();
        OrderDetail::factory()->for($order)->for($product)->create();

        $payment = Payment::factory()
            ->for($order)
            ->for($gateway)
            ->create([
                'amount' => 500,
                'currency' => 'USD',
                'status' => 'completed',
            ]);

        Refund::factory()->for($payment)->create([
            'amount' => 320,
            'currency' => 'USD',
            'status' => Refund::STATUS_COMPLETED,
            'refund_id' => 'FILTER-HIGH',
        ]);

        Refund::factory()->for($payment)->create([
            'amount' => 40,
            'currency' => 'USD',
            'status' => Refund::STATUS_PENDING,
            'refund_id' => 'FILTER-LOW',
        ]);

        $dataResponse = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('admin.refunds.getData', ['amount_min' => 100]));

        $dataResponse->assertOk();
        $this->assertCount(1, $dataResponse->json('data'));
        $this->assertSame('FILTER-HIGH', $dataResponse->json('data.0.reference'));

        $exportResponse = $this->get(route('admin.refunds.export', ['amount_min' => 100]));
        $exportResponse->assertOk();
        $content = $exportResponse->streamedContent();
        $this->assertStringContainsString('FILTER-HIGH', $content);
        $this->assertStringNotContainsString('FILTER-LOW', $content);
    }
}
