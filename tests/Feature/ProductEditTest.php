<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductEditTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test languages
        Language::factory()->create(['code' => 'en', 'name' => 'english', 'active' => 1]);
        Language::factory()->create(['code' => 'es', 'name' => 'spanish', 'active' => 1]);

        // Create test data
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
        $this->vendor = Vendor::factory()->create();
    }

    /**
     * @test
     */
    public function admin_can_view_product_edit_page()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $response = $this->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product', $product);
    }

    /**
     * @test
     */
    public function admin_can_update_product_with_valid_data()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ],
                'es' => [
                    'name' => 'Nombre de Producto Actualizado',
                    'description' => 'Descripción de producto actualizada'
                ]
            ],
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
            'variants' => [
                [
                    'name' => 'Updated Variant',
                    'price' => 99.99,
                    'discount_price' => 79.99,
                    'stock' => 50,
                    'SKU' => 'UPD-SKU-001',
                    'barcode' => '1234567890123',
                    'weight' => 1.5,
                    'dimensions' => '10x20x5'
                ]
            ]
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $updateData['category_id'],
            'brand_id' => $updateData['brand_id'],
            'vendor_id' => $updateData['vendor_id'],
        ]);
    }

    /**
     * @test
     */
    public function product_update_requires_valid_category()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ]
            ],
            'category_id' => 999,  // Non-existent category
            'vendor_id' => $this->vendor->id,
            'variants' => [
                [
                    'name' => 'Updated Variant',
                    'price' => 99.99,
                    'stock' => 50,
                    'SKU' => 'UPD-SKU-001'
                ]
            ]
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertSessionHasErrors(['category_id']);
    }

    /**
     * @test
     */
    public function product_update_requires_valid_vendor()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ]
            ],
            'category_id' => $this->category->id,
            'vendor_id' => 999,  // Non-existent vendor
            'variants' => [
                [
                    'name' => 'Updated Variant',
                    'price' => 99.99,
                    'stock' => 50,
                    'SKU' => 'UPD-SKU-001'
                ]
            ]
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertSessionHasErrors(['vendor_id']);
    }

    /**
     * @test
     */
    public function product_update_requires_at_least_one_variant()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ]
            ],
            'category_id' => $this->category->id,
            'vendor_id' => $this->vendor->id,
            'variants' => []  // Empty variants array
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertSessionHasErrors(['variants']);
    }

    /**
     * @test
     */
    public function variant_requires_valid_price()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ]
            ],
            'category_id' => $this->category->id,
            'vendor_id' => $this->vendor->id,
            'variants' => [
                [
                    'name' => 'Updated Variant',
                    'price' => -10,  // Invalid negative price
                    'stock' => 50,
                    'SKU' => 'UPD-SKU-001'
                ]
            ]
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertSessionHasErrors(['variants.0.price']);
    }

    /**
     * @test
     */
    public function variant_requires_valid_stock()
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'vendor_id' => $this->vendor->id,
        ]);

        $updateData = [
            'translations' => [
                'en' => [
                    'name' => 'Updated Product Name',
                    'description' => 'Updated product description'
                ]
            ],
            'category_id' => $this->category->id,
            'vendor_id' => $this->vendor->id,
            'variants' => [
                [
                    'name' => 'Updated Variant',
                    'price' => 99.99,
                    'stock' => -5,  // Invalid negative stock
                    'SKU' => 'UPD-SKU-001'
                ]
            ]
        ];

        $response = $this->put(route('admin.products.update', $product), $updateData);

        $response->assertSessionHasErrors(['variants.0.stock']);
    }
}
