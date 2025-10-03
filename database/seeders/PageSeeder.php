<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
                'template' => 'with-hero',
                'show_in_navigation' => true,
                'show_in_footer' => true,
                'is_featured' => true,
                'published_at' => Carbon::now()->subDays(14),
                'translations' => [
                    'en' => [
                        'title' => 'About Velstore',
                        'excerpt' => 'Velstore combines thoughtful design systems with modern commerce tooling to help founders launch quickly.',
                        'content' => '<p>Velstore is a modern commerce experience crafted for ambitious brands. We combine curated design systems with powerful merchandising tools to help teams launch quickly and scale globally.</p><p>From localized content management to dynamic product merchandising, Velstore delivers a flexible foundation that grows with your brand.</p>',
                        'meta_title' => 'About Velstore — Modern commerce for ambitious brands',
                        'meta_description' => 'Learn more about the Velstore platform, our design philosophy, and the tools that help brands grow globally.',
                    ],
                    'fr' => [
                        'title' => 'À propos de Velstore',
                        'excerpt' => 'Velstore propose une expérience e-commerce moderne pour les marques ambitieuses partout dans le monde.',
                        'content' => '<p>Velstore est une expérience commerciale moderne conçue pour les marques ambitieuses. Nous réunissons des systèmes de conception et des outils puissants afin de permettre aux équipes de se développer à l’international.</p><p>Notre plateforme offre une gestion de contenu multilingue et une vitrine modulable adaptée à votre identité.</p>',
                        'meta_title' => 'À propos de Velstore — Commerce moderne',
                        'meta_description' => 'Découvrez la plateforme Velstore et la façon dont nous aidons les marques ambitieuses à se développer à l’international.',
                    ],
                ],
            ],
            [
                'slug' => 'shipping-and-returns',
                'status' => true,
                'template' => 'default',
                'show_in_navigation' => false,
                'show_in_footer' => true,
                'is_featured' => false,
                'published_at' => Carbon::now()->subDays(10),
                'translations' => [
                    'en' => [
                        'title' => 'Shipping & Returns',
                        'excerpt' => 'Understand our delivery timelines, carriers, and return process for every order.',
                        'content' => '<p>Orders are dispatched within two business days. You can return unworn items within 30 days for a full refund. Learn more about timelines, carriers, and restocking fees inside this policy page.</p><p>International orders may require additional processing time depending on customs. We will notify you at each stage of fulfillment.</p>',
                        'meta_title' => 'Shipping & returns information',
                        'meta_description' => 'Review Velstore shipping speeds, carriers, return windows, and how to initiate a return.',
                    ],
                    'es' => [
                        'title' => 'Envíos y devoluciones',
                        'excerpt' => 'Consulta los tiempos de envío, transportistas y cómo devolver un pedido fácilmente.',
                        'content' => '<p>Los pedidos se envían en un plazo de dos días hábiles. Puedes devolver los artículos sin uso dentro de los 30 días para obtener un reembolso completo.</p><p>Los envíos internacionales pueden requerir más tiempo de procesamiento. Te informaremos en cada paso del proceso.</p>',
                        'meta_title' => 'Información de envíos y devoluciones',
                        'meta_description' => 'Revisa los plazos de envío de Velstore, los transportistas y cómo gestionar devoluciones sin complicaciones.',
                    ],
                ],
            ],
            [
                'slug' => 'privacy-policy',
                'status' => false,
                'template' => 'default',
                'show_in_navigation' => false,
                'show_in_footer' => true,
                'is_featured' => false,
                'published_at' => null,
                'translations' => [
                    'en' => [
                        'title' => 'Privacy policy',
                        'excerpt' => 'We process customer data responsibly, transparently, and with consent.',
                        'content' => '<p>We process customer data responsibly and transparently. Review the policy for details on consent, storage, and third-party processors.</p><p>This policy outlines how Velstore handles personal information, including analytics, marketing preferences, and data retention windows.</p>',
                        'meta_title' => 'Velstore privacy policy',
                        'meta_description' => 'Understand how Velstore collects, uses, and protects customer information across the platform.',
                    ],
                ],
            ],
        ];

        foreach ($pages as $pageData) {
            $page = Page::updateOrCreate([
                'slug' => $pageData['slug'],
            ], [
                'status' => $pageData['status'],
                'template' => $pageData['template'] ?? 'default',
                'show_in_navigation' => $pageData['show_in_navigation'] ?? false,
                'show_in_footer' => $pageData['show_in_footer'] ?? false,
                'is_featured' => $pageData['is_featured'] ?? false,
                'published_at' => $pageData['published_at'] ?? ($pageData['status'] ? Carbon::now() : null),
            ]);

            foreach ($pageData['translations'] as $locale => $translation) {
                Language::firstOrCreate([
                    'code' => $locale,
                ], [
                    'name' => strtoupper($locale),
                    'translated_text' => strtoupper($locale),
                    'active' => $locale === $defaultLocale,
                ]);

                $content = $translation['content'];
                $metaDescription = $translation['meta_description']
                    ?? Str::limit(strip_tags($translation['excerpt'] ?? $content), 160);

                PageTranslation::updateOrCreate([
                    'page_id' => $page->id,
                    'language_code' => $locale,
                ], [
                    'title' => $translation['title'],
                    'excerpt' => $translation['excerpt'] ?? null,
                    'content' => $content,
                    'meta_title' => $translation['meta_title'] ?? $translation['title'],
                    'meta_description' => $metaDescription,
                    'image_url' => $translation['image_url'] ?? null,
                ]);
            }
        }
    }
}
