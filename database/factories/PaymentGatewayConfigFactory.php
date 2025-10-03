<?php

namespace Database\Factories;

use App\Models\PaymentGateway;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGatewayConfig>
 */
class PaymentGatewayConfigFactory extends Factory
{
    protected $model = PaymentGatewayConfig::class;

    public function definition(): array
    {
        return [
            'gateway_id' => PaymentGateway::factory(),
            'key_name' => $this->faker->unique()->word(),
            'key_value' => $this->faker->sha256(),
            'is_encrypted' => $this->faker->boolean(20),
            'environment' => $this->faker->randomElement(['production', 'sandbox']),
        ];
    }
}
