<?php

namespace Tests\Feature\Database;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ShippingAddress;
use Database\Seeders\OrderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_seeder_populates_demo_orders_with_shipping_addresses(): void
    {
        Product::factory()->count(2)->create();

        $this->seed(OrderSeeder::class);

        $this->assertSame(4, Order::count());
        $this->assertSame(4, ShippingAddress::count());
        $this->assertSame(6, OrderDetail::count());

        $this->assertDatabaseHas('shipping_addresses', [
            'name' => 'Guest One',
            'address' => '123 Demo Street',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'name' => 'Guest Two',
            'address' => '456 Sample Avenue',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'name' => 'Showcase Customer',
            'address' => '789 Showcase Boulevard',
        ]);

        $this->assertDatabaseHas('shipping_addresses', [
            'name' => 'Guest Three',
            'address' => '321 Sample Parkway',
        ]);

        $singleShopOrder = Order::with('details.product')->findOrFail(2);
        $this->assertNotNull($singleShopOrder->shop_id);
        $this->assertSame(
            $singleShopOrder->details->pluck('product.shop_id')->filter()->unique()->first(),
            $singleShopOrder->shop_id
        );
    }

    public function test_order_seeder_is_idempotent(): void
    {
        Product::factory()->count(2)->create();

        $this->seed(OrderSeeder::class);
        $this->seed(OrderSeeder::class);

        $this->assertSame(4, Order::count());
        $this->assertSame(4, ShippingAddress::count());
        $this->assertSame(6, OrderDetail::count());
    }
}
