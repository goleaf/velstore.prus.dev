<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => 'active',
            'marketing_opt_in' => $this->faker->boolean(60),
            'loyalty_tier' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => [
            'status' => 'inactive',
        ]);
    }
}
