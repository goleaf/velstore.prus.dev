<?php

namespace Tests\Feature\Store;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttributeValue;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductVariantPriceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        app()->setLocale('en');

        StoreSetting::create([
            'key' => 'default_currency',
            'value' => 'USD',
        ]);

        Currency::factory()->create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'exchange_rate' => 1.0000,
        ]);
    }

    public function test_variant_price_endpoint_returns_expected_payload(): void
    {
        $setup = $this->createProductWithDeterministicVariants();
        $product = $setup['product'];
        $variants = $setup['variants'];

        $targetVariant = $variants['red-small'];

        $response = $this->getJson(route('product.variant.price', [
            'variant_id' => $targetVariant->id,
            'product_id' => $product->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'price' => number_format($targetVariant->price, 2),
            'stock' => 'IN STOCK',
            'is_out_of_stock' => false,
            'currency_symbol' => '$',
        ]);
    }

    public function test_variant_picker_component_renders_attribute_inputs(): void
    {
        $setup = $this->createProductWithDeterministicVariants();
        $product = $setup['product'];
        $variantMap = $setup['variantMap'];
        $attributes = $setup['attributes'];

        $view = $this->view('themes.xylo.components.product-variant-picker', [
            'product' => $product,
            'variantMap' => $variantMap,
            'inStock' => true,
        ]);

        $view->assertSee('id="product-variant-picker"', false);
        $view->assertSee('data-product-id="' . $product->id . '"', false);
        $view->assertSee('name="attribute_' . $attributes['color']->id . '"', false);
        $view->assertSee('name="attribute_' . $attributes['size']->id . '"', false);

        foreach ($product->attributeValues as $value) {
            $view->assertSee('value="' . $value->id . '"', false);
        }
    }

    /**
     * @return array{product: \App\Models\Product, variants: array<string, \App\Models\ProductVariant>, variantMap: array<int, array{id: int, attributes: array<int, int>}>, attributes: array{color: \App\Models\Attribute, size: \App\Models\Attribute}}
     */
    private function createProductWithDeterministicVariants(): array
    {
        $product = Product::factory()->create([
            'product_type' => 'variable',
            'currency' => 'USD',
            'price' => 10.00,
            'discount_price' => null,
            'stock' => 0,
        ]);

        $colorAttribute = Attribute::factory()->create(['name' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['name' => 'Size']);

        $red = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Red',
        ]);
        $blue = AttributeValue::factory()->create([
            'attribute_id' => $colorAttribute->id,
            'value' => 'Blue',
        ]);
        $small = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Small',
        ]);
        $large = AttributeValue::factory()->create([
            'attribute_id' => $sizeAttribute->id,
            'value' => 'Large',
        ]);

        $attributeValues = [$red, $blue, $small, $large];

        foreach ($attributeValues as $value) {
            AttributeValueTranslation::create([
                'attribute_value_id' => $value->id,
                'language_code' => 'en',
                'translated_value' => $value->value,
            ]);

            ProductAttributeValue::create([
                'product_id' => $product->id,
                'attribute_value_id' => $value->id,
            ]);
        }

        $definitions = [
            'red-small' => [
                'values' => [$red->id, $small->id],
                'price' => 19.99,
                'stock' => 5,
                'sku' => 'SKU-RED-SM-' . $product->id,
                'barcode' => '1000000000000',
                'is_primary' => true,
            ],
            'blue-large' => [
                'values' => [$blue->id, $large->id],
                'price' => 24.50,
                'stock' => 0,
                'sku' => 'SKU-BLU-LG-' . $product->id,
                'barcode' => '1000000000001',
                'is_primary' => false,
            ],
        ];

        $variants = [];

        foreach ($definitions as $key => $definition) {
            $variant = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'variant_slug' => $product->slug . '-' . $key,
                'price' => $definition['price'],
                'discount_price' => null,
                'stock' => $definition['stock'],
                'SKU' => $definition['sku'],
                'barcode' => $definition['barcode'],
                'is_primary' => $definition['is_primary'],
                'weight' => 1.00,
                'dimensions' => '10x10x10',
            ]);

            foreach ($definition['values'] as $valueId) {
                ProductVariantAttributeValue::create([
                    'product_variant_id' => $variant->id,
                    'attribute_value_id' => $valueId,
                    'product_id' => $product->id,
                ]);
            }

            $variants[$key] = $variant->fresh('attributeValues');
        }

        $product->load([
            'variants.attributeValues.attribute',
            'attributeValues.attribute',
            'attributeValues.translations',
        ]);

        $variantMap = $product->variants->map(static function (ProductVariant $variant): array {
            return [
                'id' => $variant->id,
                'attributes' => $variant->attributeValues->pluck('id')->sort()->values()->all(),
            ];
        })->toArray();

        return [
            'product' => $product,
            'variants' => $variants,
            'variantMap' => $variantMap,
            'attributes' => [
                'color' => $colorAttribute,
                'size' => $sizeAttribute,
            ],
        ];
    }
}
