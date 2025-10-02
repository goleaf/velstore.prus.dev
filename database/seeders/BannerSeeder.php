<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $languages = Language::where('active', 1)->get();

            $banners = [
                [
                    'type' => 'promotion',
                    'status' => 1,
                    'translations' => [
                        'title' => 'Ready to Shop',
                        'description' => 'Your one-stop shop for everything you need.',
                        'image' => 'assets/images/placeholder-banner.svg',
                    ],
                ],
            ];

            foreach ($banners as $item) {
                $banner = Banner::create([
                    'type' => $item['type'],
                    'status' => $item['status'],
                ]);

                foreach ($languages as $lang) {
                    $imagePath = public_path($item['translations']['image']);
                    $localPath = $item['translations']['image'];

                    if (! file_exists($imagePath)) {
                        $localPath = $item['translations']['image'];
                    }

                    $translatedTitle = match ($lang->code) {
                        'es' => 'Listo para comprar',
                        'de' => 'Bereit zum Einkaufen',
                        default => $item['translations']['title'],
                    };

                    $translatedDescription = match ($lang->code) {
                        'es' => 'Tu tienda única para todo lo que necesitas.',
                        'de' => 'Ihr One-Stop-Shop für alles, was Sie brauchen.',
                        default => $item['translations']['description'],
                    };

                    $banner->translations()->create([
                        'language_code' => $lang->code,
                        'title' => $translatedTitle,
                        'description' => $translatedDescription,
                        'image_url' => $localPath,
                    ]);
                }
            }
        });
    }
}
