<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $languageCodes = Language::where('active', 1)->pluck('code')->all();

            if (empty($languageCodes)) {
                return;
            }

            $now = Carbon::now();

            $bannerDefinitions = [
                [
                    'name' => 'Homepage Spotlight',
                    'type' => 'promotion',
                    'status' => 1,
                    'display_location' => 'home',
                    'priority' => 120,
                    'starts_at' => $now->copy()->subDays(3),
                    'ends_at' => $now->copy()->addWeeks(2),
                    'translations' => [
                        'en' => [
                            'title' => 'Homepage Spotlight',
                            'description' => 'Discover the latest arrivals tailored for you.',
                            'image' => 'assets/images/placeholder-banner.svg',
                            'button_text' => 'Shop Latest',
                            'button_url' => '/shop',
                        ],
                        'de' => [
                            'title' => 'Startseiten-Highlight',
                            'description' => 'Entdecken Sie die neuesten Ankünfte, die für Sie kuratiert wurden.',
                        ],
                        'es' => [
                            'title' => 'Destacado en portada',
                            'description' => 'Descubre las últimas novedades seleccionadas para ti.',
                        ],
                    ],
                ],
                [
                    'name' => 'Weekend Flash Sale',
                    'type' => 'sale',
                    'status' => 1,
                    'display_location' => 'shop',
                    'priority' => 95,
                    'starts_at' => $now->copy()->next('friday')->setTime(6, 0),
                    'ends_at' => $now->copy()->next('monday')->setTime(1, 0),
                    'translations' => [
                        'en' => [
                            'title' => 'Weekend Flash Sale',
                            'description' => '48 hours of doorbuster deals across every category.',
                            'image' => 'assets/images/placeholder-banner.svg',
                            'button_text' => 'Save Big',
                            'button_url' => '/collections/flash-sale',
                        ],
                        'de' => [
                            'title' => 'Wochenend-Blitzverkauf',
                            'description' => '48 Stunden voller Angebote in jeder Kategorie.',
                        ],
                        'es' => [
                            'title' => 'Venta relámpago de fin de semana',
                            'description' => '48 horas de ofertas imperdibles en todas las categorías.',
                        ],
                    ],
                ],
                [
                    'name' => 'New Season Essentials',
                    'type' => 'seasonal',
                    'status' => 0,
                    'display_location' => 'category',
                    'priority' => 60,
                    'starts_at' => null,
                    'ends_at' => null,
                    'translations' => [
                        'en' => [
                            'title' => 'New Season Essentials',
                            'description' => 'Refresh your wardrobe with transitional favourites.',
                            'image' => 'assets/images/placeholder-banner.svg',
                            'button_text' => 'Explore Styles',
                            'button_url' => '/collections/new-season',
                        ],
                        'de' => [
                            'title' => 'Essentials für die neue Saison',
                            'description' => 'Frischen Sie Ihre Garderobe mit Übergangs-Lieblingen auf.',
                        ],
                        'es' => [
                            'title' => 'Esenciales de la nueva temporada',
                            'description' => 'Renueva tu armario con básicos de transición.',
                        ],
                    ],
                ],
            ];

            foreach ($bannerDefinitions as $definition) {
                $defaultTranslation = $definition['translations']['en'];

                $banner = Banner::updateOrCreate(
                    ['title' => $definition['name']],
                    [
                        'type' => $definition['type'],
                        'status' => $definition['status'],
                        'display_location' => $definition['display_location'],
                        'priority' => $definition['priority'],
                        'starts_at' => $definition['starts_at'],
                        'ends_at' => $definition['ends_at'],
                    ]
                );

                foreach ($languageCodes as $code) {
                    $translation = $definition['translations'][$code] ?? $defaultTranslation;

                    BannerTranslation::updateOrCreate(
                        [
                            'banner_id' => $banner->id,
                            'language_code' => $code,
                        ],
                        [
                            'title' => $translation['title'],
                            'description' => $translation['description'],
                            'button_text' => $translation['button_text'] ?? $defaultTranslation['button_text'] ?? null,
                            'button_url' => $translation['button_url'] ?? $defaultTranslation['button_url'] ?? null,
                            'image_url' => $translation['image'] ?? $defaultTranslation['image'],
                        ]
                    );
                }
            }
        });
    }
}
