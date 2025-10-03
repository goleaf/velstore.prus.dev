<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_seeder_populates_sample_pages(): void
    {
        $this->seed(LanguageSeeder::class);
        $this->seed(PageSeeder::class);

        $this->assertDatabaseHas('pages', [
            'slug' => 'about-us',
            'status' => true,
        ]);

        $this->assertDatabaseHas('page_translations', [
            'language_code' => 'en',
            'title' => 'About Velstore',
        ]);

        $this->assertDatabaseHas('page_translations', [
            'language_code' => 'es',
            'title' => 'Envíos y devoluciones',
        ]);
    }
}
