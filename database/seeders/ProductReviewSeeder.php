<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::query()->get();

        if ($customers->isEmpty()) {
            $customers = Customer::factory()->count(5)->create();
        }

        $customerIds = $customers->pluck('id');
        $products = Product::query()->get();

        if ($products->isEmpty()) {
            return;
        }

        $faker = fake();

        foreach ($products as $product) {
            $existingCount = ProductReview::where('product_id', $product->id)->count();
            $targetCount = max(5, $existingCount);

            if ($existingCount >= $targetCount) {
                continue;
            }

            $assignedCustomerIds = ProductReview::where('product_id', $product->id)->pluck('customer_id')->all();
            $missing = $targetCount - $existingCount;

            ProductReview::factory()
                ->count($missing)
                ->state(function () use ($product, $customerIds, &$assignedCustomerIds, $faker) {
                    $available = collect($customerIds)->diff($assignedCustomerIds);

                    if ($available->isEmpty()) {
                        $available = $customerIds;
                    }

                    $customerId = $available->random();
                    $assignedCustomerIds[] = $customerId;

                    return [
                        'product_id' => $product->id,
                        'customer_id' => $customerId,
                        'is_approved' => $faker->boolean(70),
                        'rating' => $faker->numberBetween(1, 5),
                        'review' => $faker->boolean(80) ? $faker->sentences(asText: true) : null,
                        'created_at' => now()->subDays($faker->numberBetween(0, 90)),
                        'updated_at' => now(),
                    ];
                })
                ->create();
        }
    }
}
