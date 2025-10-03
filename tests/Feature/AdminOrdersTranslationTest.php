<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class AdminOrdersTranslationTest extends TestCase
{
    public function test_all_locales_provide_orders_translations(): void
    {
        $baseLocale = config('app.fallback_locale', 'en');
        $expected = Lang::get('cms.orders', [], $baseLocale);

        $this->assertIsArray($expected, 'Expected base order translations to be an array.');

        $locales = collect(glob(lang_path('*'), GLOB_ONLYDIR))
            ->map(fn (string $path) => basename($path))
            ->filter(fn (string $locale) => $locale !== $baseLocale)
            ->values();

        foreach ($locales as $locale) {
            $translations = Lang::get('cms.orders', [], $locale);
            $this->assertIsArray($translations, "Orders translations for locale [{$locale}] should be an array.");

            foreach ($expected as $key => $_) {
                $this->assertArrayHasKey(
                    $key,
                    $translations,
                    sprintf('Locale [%s] is missing the orders translation key [%s].', $locale, $key)
                );
            }
        }
    }
}
