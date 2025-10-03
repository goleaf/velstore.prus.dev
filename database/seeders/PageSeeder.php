<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLocale = config('app.locale', 'en');

        if (!Language::where('code', $defaultLocale)->exists()) {
            Language::firstOrCreate([
                'code' => $defaultLocale,
            ], [
                'name' => strtoupper($defaultLocale),
                'translated_text' => strtoupper($defaultLocale),
                'active' => true,
            ]);
        }

        $pages = [
            [
                'slug' => 'about-us',
                'status' => true,
                'translations' => [
                    'en' => [
                        'title' => 'About Velstore',
                        'content' => '<p>Velstore is a modern commerce experience crafted for ambitious brands. We combine curated design systems with powerful merchandising tools to help teams launch quickly and scale globally.</p>',
                    ],
                    'fr' => [
                        'title' => 'À propos de Velstore',
                        'content' => '<p>Velstore est une expérience commerciale moderne conçue pour les marques ambitieuses. Nous réunissons des systèmes de conception et des outils puissants afin de permettre aux équipes de se développer à l’international.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'shipping-and-returns',
                'status' => true,
                'translations' => [
                    'en' => [
                        'title' => 'Shipping & Returns',
                        'content' => '<p>Orders are dispatched within two business days. You can return unworn items within 30 days for a full refund. Learn more about timelines, carriers, and restocking fees inside this policy page.</p>',
                    ],
                    'es' => [
                        'title' => 'Envíos y devoluciones',
                        'content' => '<p>Los pedidos se envían en un plazo de dos días hábiles. Puedes devolver los artículos sin uso dentro de los 30 días para obtener un reembolso completo.</p>',
                    ],
                ],
            ],
            [
                'slug' => 'privacy-policy',
                'status' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Privacy policy',
                        'content' => '<p>We process customer data responsibly and transparently. Review the policy for details on consent, storage, and third-party processors.</p>',
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::updateOrCreate([
                'slug' => $pageData['slug'],
            ], [
                'status' => $pageData['status'],
            ]);

            foreach ($pageData['translations'] as $locale => $translation) {
                Language::firstOrCreate([
                    'code' => $locale,
                ], [
                    'name' => strtoupper($locale),
                    'translated_text' => strtoupper($locale),
                    'active' => $locale === $defaultLocale,
                ]);

                PageTranslation::updateOrCreate([
                    'page_id' => $page->id,
                    'language_code' => $locale,
                ], [
                    'title' => $translation['title'],
                    'content' => $translation['content'],
                    'image_url' => $translation['image_url'] ?? null,
                ]);
            }
        }
    }
}
