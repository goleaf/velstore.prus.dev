<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantDemoSeeder extends Seeder
{
    public function run(): void
    {
        $languages = collect([
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'es', 'name' => 'Spanish'],
        ])->map(function (array $data) {
            return Language::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'translated_text' => $data['name'],
                    'active' => true,
                ]
            );
        });

        $product = Product::firstWhere('slug', 'demo-product-variant');

        if (! $product) {
            $product = Product::factory()->create([
                'slug' => 'demo-product-variant',
                'SKU' => 'DEMO-PRODUCT',
                'product_type' => 'variable',
                'status' => 1,
            ]);
        }

        foreach ($languages as $language) {
            $product->translations()->updateOrCreate(
                ['language_code' => $language->code],
                [
                    'locale' => $language->code,
                    'name' => 'Demo Product '.Str::upper($language->code),
                    'description' => 'Demo description for '.$language->name.' shoppers.',
                    'short_description' => 'Demo short description',
                    'tags' => 'demo,variant',
                ]
            );
        }

        $variantSlug = 'demo-variant-'.$product->id;

        $variant = ProductVariant::firstWhere('variant_slug', $variantSlug);

        if (! $variant) {
            $variant = ProductVariant::factory()
                ->for($product)
                ->create([
                    'variant_slug' => $variantSlug,
                    'SKU' => 'DEMO-'.$product->id,
                    'is_primary' => true,
                ]);
        }

        foreach ($languages as $language) {
            $variant->translations()->updateOrCreate(
                ['language_code' => $language->code],
                [
                    'name' => 'Demo Variant '.Str::upper($language->code),
                ]
            );
        }
    }
}
