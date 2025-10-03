<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'gateway_id' => PaymentGateway::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'currency' => $this->faker->currencyCode(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'refunded']),
            'transaction_id' => $this->faker->uuid(),
            'response' => ['transaction' => $this->faker->uuid()],
            'meta' => ['ip' => $this->faker->ipv4()],
        ];
    }
}
