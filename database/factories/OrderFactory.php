<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $itemsTotal = $this->faker->randomFloat(2, 50, 400);
        $shippingAmount = $this->faker->randomFloat(2, 0, 25);
        $discountAmount = $this->faker->randomFloat(2, 0, 30);
        $taxAmount = $this->faker->randomFloat(2, 0, 35);
        $adjustmentAmount = $this->faker->randomFloat(2, 0, 15);

        $grandTotal = round($itemsTotal + $shippingAmount - $discountAmount + $taxAmount + $adjustmentAmount, 2);

        return [
            'shop_id' => Shop::factory(),
            'customer_id' => Customer::factory(),
            'guest_email' => null,
            'total_amount' => max($grandTotal, 0.01),
            'currency' => 'USD',
            'shipping_method' => $this->faker->randomElement(['Standard Shipping', 'Express Courier', 'Local Pickup']),
            'shipping_tracking_number' => $this->faker->regexify('TRK[0-9]{8}'),
            'shipping_estimated_at' => $this->faker->dateTimeBetween('now', '+10 days'),
            'shipping_amount' => $shippingAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'adjustment_amount' => $adjustmentAmount,
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'canceled']),
        ];
    }
}
