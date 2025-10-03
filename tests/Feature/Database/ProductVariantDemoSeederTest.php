<?php

namespace Tests\Feature\Database;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\ProductVariantDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_demo_variant_with_translations(): void
    {
        $this->seed(ProductVariantDemoSeeder::class);

        $this->assertDatabaseHas('languages', [
            'code' => 'en',
            'active' => true,
        ]);

        $product = Product::first();
        $this->assertNotNull($product);
        $this->assertSame('variable', $product->product_type);

        foreach (['en', 'es'] as $code) {
            $this->assertTrue(
                $product->translations()->where('language_code', $code)->exists(),
                "Product translation missing for language code {$code}"
            );
        }

        $variant = ProductVariant::first();
        $this->assertNotNull($variant);
        $this->assertTrue($variant->translations()->where('language_code', 'en')->exists());
        $this->assertTrue($variant->translations()->where('language_code', 'es')->exists());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ProductVariantDemoSeeder::class);
        $this->seed(ProductVariantDemoSeeder::class);

        $this->assertSame(1, Language::where('code', 'en')->count());
        $this->assertSame(1, Language::where('code', 'es')->count());
        $this->assertSame(1, Product::count());
        $this->assertSame(1, ProductVariant::count());
    }
}
