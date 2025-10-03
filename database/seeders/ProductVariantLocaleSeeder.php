<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class ProductVariantLocaleSeeder extends Seeder
{
    /**
     * Ensure the primary product variant locales are present and active.
     */
    public function run(): void
    {
        $locales = [
            ['code' => 'en', 'name' => 'English', 'translated_text' => 'English'],
            ['code' => 'de', 'name' => 'German', 'translated_text' => 'Deutsch'],
            ['code' => 'es', 'name' => 'Spanish', 'translated_text' => 'Español'],
        ];

        foreach ($locales as $locale) {
            Language::updateOrCreate(
                ['code' => $locale['code']],
                [
                    'name' => $locale['name'],
                    'translated_text' => $locale['translated_text'],
                    'active' => true,
                ]
            );
        }
    }
}

