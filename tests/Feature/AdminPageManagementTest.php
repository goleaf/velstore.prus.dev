<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_pages_index(): void
    {
        $admin = User::factory()->create();
        $page = Page::factory()->create();
        PageTranslation::factory()->for($page)->create([
            'language_code' => config('app.locale'),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.pages.index'));

        $response
            ->assertOk()
            ->assertViewIs('admin.pages.index')
            ->assertViewHas('pages', function ($pages) use ($page) {
                return $pages->contains('id', $page->id);
            });
    }

    public function test_admin_can_view_create_page_form(): void
    {
        $admin = User::factory()->create();
        $language = Language::factory()->create(['code' => config('app.locale')]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.pages.create'));

        $response
            ->assertOk()
            ->assertViewIs('admin.pages.create')
            ->assertViewHas('activeLanguages', function ($languages) use ($language) {
                return $languages->contains('id', $language->id);
            });
    }

    public function test_admin_can_store_new_page_with_translations(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $defaultLocale = config('app.locale');
        Language::factory()->create(['code' => $defaultLocale]);
        Language::factory()->create(['code' => 'fr']);

        $payload = [
            'status' => 1,
            'translations' => [
                $defaultLocale => [
                    'title' => 'About Us',
                    'content' => 'Learn more about our journey.',
                    'image' => UploadedFile::fake()->image('about.jpg'),
                ],
                'fr' => [
                    'title' => 'À propos de nous',
                    'content' => 'En savoir plus sur notre parcours.',
                    'image' => UploadedFile::fake()->image('a-propos.jpg'),
                ],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->post(route('admin.pages.store'), $payload);

        $response->assertRedirect(route('admin.pages.index'));

        $page = Page::where('slug', 'about-us')->first();
        $this->assertNotNull($page);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 1,
        ]);

        $translations = PageTranslation::where('page_id', $page->id)->get();
        $this->assertCount(2, $translations);

        foreach ($translations as $translation) {
            $this->assertDatabaseHas('page_translations', [
                'page_id' => $page->id,
                'language_code' => $translation->language_code,
                'title' => $translation->title,
            ]);

            if ($translation->image_url) {
                Storage::disk('public')->assertExists($translation->image_url);
            }
        }
    }

    public function test_admin_can_update_page_translations_and_status(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $defaultLocale = config('app.locale');
        Language::factory()->create(['code' => $defaultLocale]);

        $page = Page::factory()->create(['status' => 1, 'slug' => 'about-us']);
        Storage::disk('public')->put('pages/old-image.jpg', 'old-image');
        PageTranslation::factory()->for($page)->create([
            'language_code' => $defaultLocale,
            'title' => 'Old Title',
            'content' => 'Old content',
            'image_url' => 'pages/old-image.jpg',
        ]);

        $payload = [
            'status' => 0,
            'translations' => [
                $defaultLocale => [
                    'title' => 'New Title',
                    'content' => 'Updated content for the page.',
                    'image' => UploadedFile::fake()->image('new-image.jpg'),
                ],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->put(route('admin.pages.update', $page), $payload);

        $response->assertRedirect(route('admin.pages.index'));

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'status' => 0,
        ]);

        $translation = PageTranslation::where('page_id', $page->id)
            ->where('language_code', $defaultLocale)
            ->first();

        $this->assertNotNull($translation);
        $this->assertSame('New Title', $translation->title);
        $this->assertSame('Updated content for the page.', $translation->content);
        $this->assertNotNull($translation->image_url);

        Storage::disk('public')->assertMissing('pages/old-image.jpg');
        Storage::disk('public')->assertExists($translation->image_url);
    }

    public function test_admin_can_delete_page(): void
    {
        $admin = User::factory()->create();
        $page = Page::factory()->create();
        PageTranslation::factory()->for($page)->create([
            'language_code' => config('app.locale'),
        ]);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.pages.destroy', $page));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseMissing('page_translations', ['page_id' => $page->id]);
    }

    public function test_admin_can_update_page_status_via_ajax(): void
    {
        $admin = User::factory()->create();
        $page = Page::factory()->create(['status' => 1]);

        $this->actingAs($admin);

        $response = $this->postJson(route('admin.pages.updateStatus'), [
            'id' => $page->id,
            'status' => 0,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertFalse($page->fresh()->status);
    }

    public function test_pages_datatable_returns_page_data(): void
    {
        $admin = User::factory()->create();
        $page = Page::factory()->create();
        PageTranslation::factory()->for($page)->create([
            'language_code' => config('app.locale'),
            'title' => 'About Us',
        ]);

        $this->actingAs($admin);

        $response = $this->postJson(route('admin.pages.data'), [
            'draw' => 1,
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'translated_title' => 'About Us',
            ]);
    }
}
