<?php

namespace Tests\Feature\Database;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingAddress;
use Database\Seeders\PaymentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_seeder_creates_demo_payment_with_shipping_address(): void
    {
        $this->seed(PaymentSeeder::class);

        $order = Order::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($order);

        $this->assertTrue($order->shippingAddress()->exists());
        $this->assertDatabaseHas('shipping_addresses', [
            'order_id' => $order->id,
            'name' => 'Guest Checkout',
        ]);

        $this->assertTrue($order->payments()->exists());
        $this->assertSame(1, Payment::count());
    }

    public function test_payment_seeder_is_idempotent(): void
    {
        $this->seed(PaymentSeeder::class);
        $this->seed(PaymentSeeder::class);

        $order = Order::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($order);

        $this->assertSame(1, ShippingAddress::count());
        $this->assertSame(1, Payment::count());
    }
}
