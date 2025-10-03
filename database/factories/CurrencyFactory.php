<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        $code = strtoupper($this->faker->unique()->currencyCode());

        return [
            'name' => $this->faker->currencyCode(),
            'code' => $code,
            'symbol' => $this->faker->randomElement(['$', '€', '£', '¥', '₹']),
            'exchange_rate' => $this->faker->randomFloat(4, 0.5, 2.0),
        ];
    }
}
