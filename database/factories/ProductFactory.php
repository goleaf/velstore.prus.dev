<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 500);

        $dimensions = $this->faker->numberBetween(10, 100)
            . 'x' . $this->faker->numberBetween(10, 100)
            . 'x' . $this->faker->numberBetween(10, 100);

        return [
            'shop_id' => null,
            'vendor_id' => null,
            'seller_id' => null,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'category_id' => null,
            'brand_id' => null,
            'product_type' => 'simple',
            'price' => $price,
            'discount_price' => $this->faker->optional()->randomFloat(2, 1, $price),
            'stock' => $this->faker->numberBetween(0, 100),
            'currency' => $this->faker->currencyCode(),
            'SKU' => Str::upper($this->faker->unique()->bothify('SKU-#####')),
            'weight' => $this->faker->optional()->randomFloat(2, 0.1, 5.0),
            'dimensions' => $this->faker->optional()->regexify('\\d{1,2}x\\d{1,2}x\\d{1,2}'),
            'image_url' => null,
            'status' => 1,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            if (! $product->vendor_id) {
                $vendor = Vendor::factory()->create();
                $product->vendor_id = $vendor->id;
            }

            if (! $product->seller_id) {
                $product->seller_id = $product->vendor_id;
            }

            if (! $product->shop_id) {
                $product->shop_id = Shop::factory()->create([
                    'vendor_id' => $product->vendor_id,
                ])->id;
            }

            if (! $product->category_id) {
                $product->category_id = Category::factory()->create()->id;
            }

            if (! $product->brand_id) {
                $product->brand_id = Brand::factory()->create()->id;
            }
        })->afterCreating(function (Product $product) {
            $updates = [];

            if (! $product->shop_id) {
                $shop = Shop::factory()->create([
                    'vendor_id' => $product->vendor_id,
                ]);

                $updates['shop_id'] = $shop->id;
            }

            if (! $product->seller_id) {
                $updates['seller_id'] = $product->vendor_id;
            }

            if (! $product->category_id) {
                $updates['category_id'] = Category::factory()->create()->id;
            }

            if (! $product->brand_id) {
                $updates['brand_id'] = Brand::factory()->create()->id;
            }

            if ($updates) {
                $product->update($updates);
            }
        });
    }
}
