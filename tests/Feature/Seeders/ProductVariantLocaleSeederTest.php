<?php

namespace Tests\Feature\Seeders;

use App\Models\Language;
use Database\Seeders\ProductVariantLocaleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantLocaleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_activates_core_product_variant_locales(): void
    {
        Language::factory()->create([
            'code' => 'en',
            'name' => 'English (Existing)',
            'translated_text' => 'English',
            'active' => false,
        ]);

        $this->assertSame(1, Language::count());

        $this->seed(ProductVariantLocaleSeeder::class);

        $activeLocales = Language::whereIn('code', ['en', 'de', 'es'])->get();

        $this->assertCount(3, $activeLocales);
        $this->assertTrue($activeLocales->every(fn (Language $language): bool => (bool) $language->active));
        $this->assertEquals(
            ['Deutsch', 'English', 'Español'],
            $activeLocales->sortBy('code')->pluck('translated_text')->values()->all()
        );
    }
}

