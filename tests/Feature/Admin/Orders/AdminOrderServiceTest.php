<?php

namespace Tests\Feature\Admin\Orders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Admin\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdminOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_orders_with_calculated_totals_and_shipping(): void
    {
        $shop = Shop::factory()->create();
        $productA = Product::factory()->for($shop)->create(['price' => 20.5]);
        $productB = Product::factory()->for($shop)->create(['price' => 9.99]);

        /** @var OrderService $service */
        $service = app(OrderService::class);

        $order = $service->create([
            'shop_id' => $shop->id,
            'status' => 'processing',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2],
                ['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 12.34],
            ],
            'shipping' => [
                'name' => 'Admin Tester',
                'phone' => '+1-555-555-0000',
                'address' => '123 Admin Street',
                'city' => 'Testville',
                'postal_code' => '12345',
                'country' => 'Testland',
            ],
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame($shop->id, $order->shop_id);
        $this->assertSame('processing', $order->status);
        $this->assertSame('53.34', (string) $order->total_amount);
        $this->assertCount(2, $order->details);

        $orderDetail = $order->details->firstWhere('product_id', $productA->id);
        $this->assertNotNull($orderDetail);
        $this->assertSame(2, $orderDetail->quantity);
        $this->assertSame('20.50', (string) $orderDetail->price);

        $shipping = $order->shippingAddress;
        $this->assertNotNull($shipping);
        $this->assertSame('Admin Tester', $shipping->name);
        $this->assertSame('Testville', $shipping->city);
    }

    public function test_it_rejects_products_from_other_shops(): void
    {
        $shop = Shop::factory()->create();
        $otherShop = Shop::factory()->create();

        $productA = Product::factory()->for($shop)->create(['price' => 30]);
        $productB = Product::factory()->for($otherShop)->create(['price' => 15]);

        /** @var OrderService $service */
        $service = app(OrderService::class);

        try {
            $service->create([
                'shop_id' => $shop->id,
                'status' => 'pending',
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 1],
                    ['product_id' => $productB->id, 'quantity' => 1],
                ],
            ]);

            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());
        }

        $this->assertSame(0, Order::count());
    }

    public function test_it_requires_positive_totals_even_with_zero_unit_price(): void
    {
        $shop = Shop::factory()->create();
        $product = Product::factory()->for($shop)->create(['price' => 0]);

        /** @var OrderService $service */
        $service = app(OrderService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->create([
                'shop_id' => $shop->id,
                'status' => 'pending',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 0],
                ],
            ]);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('items', $exception->errors());

            throw $exception;
        }
    }
}
