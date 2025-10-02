<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $languages = Language::where('active', 1)->pluck('code')->toArray();

            $categories = [
                [
                    'slug' => 'electronics',
                    'parent_slug' => null,
                    'translations' => [
                        'en' => ['name' => 'Electronics', 'description' => 'Electronic devices'],
                        'fr' => ['name' => 'Électronique', 'description' => 'Appareils électroniques'],
                        'es' => ['name' => 'Electrónica', 'description' => 'Dispositivos electrónicos'],
                    ],
                    'image' => 'assets/images/placeholder-promo.svg',
                ],
                [
                    'slug' => 'fashion',
                    'parent_slug' => null,
                    'translations' => [
                        'en' => ['name' => 'Fashion', 'description' => 'Clothing and accessories'],
                        'fr' => ['name' => 'Mode', 'description' => 'Vêtements et accessoires'],
                        'es' => ['name' => 'Moda', 'description' => 'Ropa y accesorios'],
                    ],
                    'image' => 'assets/images/placeholder-promo.svg',
                ],
                [
                    'slug' => 'smartphones',
                    'parent_slug' => 'electronics',
                    'translations' => [
                        'en' => ['name' => 'Smartphones', 'description' => 'Latest mobile phones'],
                        'fr' => ['name' => 'Smartphones', 'description' => 'Derniers téléphones mobiles'],
                        'es' => ['name' => 'Smartphones', 'description' => 'Últimos teléfonos móviles'],
                    ],
                    'image' => 'assets/images/placeholder-promo.svg',
                ],
                [
                    'slug' => 't-shirts',
                    'parent_slug' => 'fashion',
                    'translations' => [
                        'en' => ['name' => 'T-Shirts', 'description' => 'Casual wear t-shirts'],
                        'fr' => ['name' => 'T-shirts', 'description' => 'T-shirts décontractés'],
                        'es' => ['name' => 'Camisetas', 'description' => 'Camisetas informales'],
                    ],
                    'image' => 'assets/images/placeholder-promo.svg',
                ],
            ];

            foreach ($categories as $categoryData) {
                $parentId = $categoryData['parent_slug']
                    ? Category::where('slug', $categoryData['parent_slug'])->value('id')
                    : null;

                $category = Category::firstOrCreate(
                    ['slug' => $categoryData['slug']],
                    [
                        'parent_category_id' => $parentId,
                        'status' => true,
                    ]
                );

                $localPath = $categoryData['image'];

                foreach ($languages as $lang) {
                    $translation = $categoryData['translations'][$lang] ?? $categoryData['translations']['en'];

                    $category->translations()->updateOrCreate(
                        ['language_code' => $lang],
                        [
                            'name' => $translation['name'],
                            'description' => $translation['description'],
                            'image_url' => $localPath,
                        ]
                    );
                }
            }
        });
    }
}
