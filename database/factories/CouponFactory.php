<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('SAVE##??')),
            'discount' => $this->faker->numberBetween(5, 50),
            'type' => $this->faker->randomElement(['percentage', 'fixed']),
            'minimum_spend' => $this->faker->optional()->randomFloat(2, 20, 200),
            'usage_limit' => $this->faker->optional()->numberBetween(50, 500),
            'usage_count' => 0,
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
