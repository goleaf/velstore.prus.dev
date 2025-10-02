<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingMenuId = DB::table('menus')->where('title', 'Main Menu')->value('id');

        if (! $existingMenuId) {
            $menuId = DB::table('menus')->insertGetId([
                'title' => 'Main Menu',
                'status' => true,
                'date' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } else {
            DB::table('menus')->where('id', $existingMenuId)->update([
                'status' => true,
                'date' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $menuId = $existingMenuId;
        }

        $menuItems = [
            ['slug' => 'home', 'order_number' => 1, 'parent_id' => null],
            ['slug' => 'about', 'order_number' => 2, 'parent_id' => null],
            ['slug' => 'services', 'order_number' => 3, 'parent_id' => null],
            ['slug' => 'blog', 'order_number' => 4, 'parent_id' => null],
            ['slug' => 'contact', 'order_number' => 5, 'parent_id' => null],
        ];

        foreach ($menuItems as &$item) {
            $item['menu_id'] = $menuId;
            $item['created_at'] = Carbon::now();
            $item['updated_at'] = Carbon::now();
        }

        // Upsert menu items by unique slug to keep operation idempotent
        DB::table('menu_items')->upsert(
            $menuItems,
            ['slug'],
            ['menu_id', 'order_number', 'parent_id', 'updated_at']
        );

        $insertedMenuItems = DB::table('menu_items')->where('menu_id', $menuId)->get();

        $translations = [];
        $languages = [
            'en' => ['Home', 'About Us', 'Our Services', 'Blog', 'Contact Us'],
            'fr' => ['Accueil', 'À propos', 'Nos services', 'Blog', 'Contact'],
            'es' => ['Inicio', 'Sobre nosotros', 'Nuestros servicios', 'Blog', 'Contacto'],
        ];

        foreach ($insertedMenuItems as $index => $menuItem) {
            foreach ($languages as $code => $titles) {
                $translations[] = [
                    'menu_item_id' => $menuItem->id,
                    'language_code' => $code,
                    'title' => $titles[$index],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        // Upsert translations to avoid duplicates
        DB::table('menu_item_translations')->upsert(
            $translations,
            ['menu_item_id', 'language_code'],
            ['title', 'updated_at']
        );
    }
}
