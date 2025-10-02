<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'amount' => $this->faker->randomFloat(2, 5, 200),
            'currency' => $this->faker->currencyCode(),
            'status' => $this->faker->randomElement(['pending', 'processed', 'failed']),
            'refund_id' => $this->faker->uuid(),
            'reason' => $this->faker->sentence(),
            'response' => ['reference' => $this->faker->uuid()],
        ];
    }
}
