<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\BannerTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.locale' => 'en']);
    }

    public function test_admin_can_view_banner_index_page(): void
    {
        $admin = User::factory()->create();
        Language::factory()->create(['code' => config('app.locale'), 'active' => true]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.banners.index'));

        $response->assertOk()
            ->assertViewIs('admin.banners.index');
    }

    public function test_datatable_endpoint_returns_banner_rows(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $locale = config('app.locale');
        Language::factory()->create(['code' => $locale, 'active' => true]);

        $banner = Banner::factory()->create([
            'type' => 'promotion',
            'status' => 1,
        ]);

        Storage::disk('public')->put('banner_images/sample-banner.jpg', 'banner');

        BannerTranslation::create([
            'banner_id' => $banner->id,
            'language_code' => $locale,
            'title' => 'Homepage Hero',
            'description' => 'Showcase banner copy',
            'image_url' => 'banner_images/sample-banner.jpg',
        ]);

        $this->actingAs($admin);
        $this->withSession(['_token' => 'test-token']);

        $response = $this->post(route('admin.banners.data'), ['_token' => 'test-token']);

        $response->assertOk();
        $data = $response->json('data');

        $this->assertNotEmpty($data);
        $firstRow = $data[0];

        $this->assertSame($banner->id, $firstRow['id']);
        $this->assertArrayHasKey('type_badge', $firstRow);
        $this->assertArrayHasKey('status', $firstRow);
    }

    public function test_admin_can_store_banner_with_multiple_languages(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $defaultLocale = config('app.locale');
        Language::factory()->create(['code' => $defaultLocale, 'active' => true]);
        Language::factory()->create(['code' => 'de', 'active' => true]);

        $payload = [
            'type' => 'promotion',
            'status' => 1,
            'languages' => [
                $defaultLocale => [
                    'title' => 'Launch Week Offers',
                    'description' => 'Celebrate our store launch with limited-time deals.',
                    'image' => UploadedFile::fake()->image('banner-en.jpg'),
                ],
                'de' => [
                    'title' => 'Angebote zur Markteinführung',
                    'description' => 'Feiern Sie unseren Store-Start mit zeitlich begrenzten Angeboten.',
                    'image' => UploadedFile::fake()->image('banner-de.jpg'),
                ],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->post(route('admin.banners.store'), $payload);

        $response->assertRedirect(route('admin.banners.index'));

        $banner = Banner::latest('id')->first();
        $this->assertNotNull($banner);
        $this->assertSame('promotion', $banner->type);
        $this->assertSame(1, (int) $banner->status);

        $translations = BannerTranslation::where('banner_id', $banner->id)->get();
        $this->assertCount(2, $translations);

        foreach ($translations as $translation) {
            Storage::disk('public')->assertExists($translation->image_url);
        }
    }

    public function test_admin_can_update_banner_and_create_missing_translations(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        $defaultLocale = config('app.locale');
        Language::factory()->create(['code' => $defaultLocale, 'active' => true]);
        Language::factory()->create(['code' => 'de', 'active' => true]);

        $banner = Banner::factory()->create([
            'type' => 'promotion',
            'status' => 1,
            'title' => 'Old Title',
        ]);

        Storage::disk('public')->put('banner_images/original.jpg', 'original');

        BannerTranslation::create([
            'banner_id' => $banner->id,
            'language_code' => $defaultLocale,
            'title' => 'Old Title',
            'description' => 'Original description',
            'image_url' => 'banner_images/original.jpg',
        ]);

        $payload = [
            'type' => 'sale',
            'status' => 0,
            'languages' => [
                [
                    'language_code' => $defaultLocale,
                    'title' => 'Updated Title',
                    'description' => 'Updated description copy.',
                    'image' => UploadedFile::fake()->image('banner-updated.jpg'),
                ],
                [
                    'language_code' => 'de',
                    'title' => 'Aktualisierter Titel',
                    'description' => 'Aktualisierte Beschreibungszeile.',
                ],
            ],
        ];

        $this->actingAs($admin);

        $response = $this->put(route('admin.banners.update', $banner->id), $payload);

        $response->assertRedirect(route('admin.banners.index'));

        $banner->refresh();
        $this->assertSame('sale', $banner->type);
        $this->assertSame(0, (int) $banner->status);
        $this->assertSame('Updated Title', $banner->title);

        $englishTranslation = BannerTranslation::where('banner_id', $banner->id)
            ->where('language_code', $defaultLocale)
            ->firstOrFail();

        $germanTranslation = BannerTranslation::where('banner_id', $banner->id)
            ->where('language_code', 'de')
            ->firstOrFail();

        $this->assertSame('Updated description copy.', $englishTranslation->description);
        $this->assertSame('Aktualisierter Titel', $germanTranslation->title);
        $this->assertSame('Aktualisierte Beschreibungszeile.', $germanTranslation->description);

        Storage::disk('public')->assertMissing('banner_images/original.jpg');
        Storage::disk('public')->assertExists($englishTranslation->image_url);
        $this->assertSame($englishTranslation->image_url, $germanTranslation->image_url);
    }
}
