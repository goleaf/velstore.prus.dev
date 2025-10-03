<?php

namespace Tests\Feature\Admin\ProductVariants;

use App\Models\Product;
use Database\Seeders\ProductVariantDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantCreatePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_displays_seeded_products_and_languages(): void
    {
        $this->seed(ProductVariantDemoSeeder::class);

        $response = $this->get(route('admin.product_variants.create'));

        $response->assertOk();
        $response->assertViewHas('products');
        $response->assertViewHas('languages');

        $product = Product::first();
        $this->assertNotNull($product, 'A demo product should be available after seeding.');

        $translatedName = optional($product->translations()->firstWhere('language_code', app()->getLocale()))?->name
            ?? $product->translations()->first()?->name;

        if ($translatedName) {
            $response->assertSee($translatedName);
        }
    }
}
