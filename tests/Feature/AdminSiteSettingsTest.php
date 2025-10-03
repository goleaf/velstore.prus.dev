<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');
        config(['app.locale' => 'en']);
    }

    public function test_admin_can_view_site_settings_overview(): void
    {
        $admin = User::factory()->create();
        $settings = SiteSetting::factory()->create([
            'site_name' => 'Velstore Pro',
            'maintenance_mode' => true,
            'maintenance_message' => 'Scheduled upgrade in progress.',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.site-settings.index'));

        $response->assertOk();
        $response->assertViewIs('admin.site-settings.index');
        $response->assertSee('Site Settings Overview');
        $response->assertSee('Velstore Pro');
        $response->assertSee('Scheduled upgrade in progress.');
        $response->assertSee('Enabled');
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->create();
        $settings = SiteSetting::factory()->create([
            'site_name' => 'Velstore',
            'maintenance_mode' => false,
        ]);

        $this->actingAs($admin);

        $payload = [
            'site_name' => 'Velstore Enterprise',
            'tagline' => 'Commerce without compromise',
            'meta_title' => 'Velstore Enterprise',
            'meta_description' => 'The premium Velstore experience.',
            'meta_keywords' => 'velstore, enterprise',
            'contact_email' => 'hello@velstore.test',
            'support_email' => 'support@velstore.test',
            'contact_phone' => '+1 555 0100',
            'support_hours' => 'Always on',
            'address' => '1 Commerce Way',
            'logo' => 'images/logo-enterprise.svg',
            'favicon' => 'images/favicon-enterprise.ico',
            'primary_color' => '#123456',
            'secondary_color' => '#abcdef',
            'facebook_url' => 'https://facebook.com/velstore',
            'twitter_url' => 'https://twitter.com/velstore',
            'instagram_url' => 'https://instagram.com/velstore',
            'linkedin_url' => 'https://linkedin.com/company/velstore',
            'maintenance_mode' => '1',
            'maintenance_message' => 'We are shipping something special.',
            'footer_text' => '© Velstore Enterprise',
        ];

        $response = $this->put(route('admin.site-settings.update'), $payload);

        $response->assertRedirect(route('admin.site-settings.index'));

        $this->assertDatabaseHas('site_settings', [
            'id' => $settings->id,
            'site_name' => 'Velstore Enterprise',
            'support_email' => 'support@velstore.test',
            'maintenance_mode' => true,
        ]);
    }

    public function test_maintenance_message_is_required_when_mode_enabled(): void
    {
        $admin = User::factory()->create();
        SiteSetting::factory()->create([
            'site_name' => 'Velstore',
        ]);

        $this->actingAs($admin);

        $payload = [
            'site_name' => 'Velstore',
            'maintenance_mode' => '1',
            'maintenance_message' => null,
        ];

        $response = $this
            ->from(route('admin.site-settings.edit'))
            ->put(route('admin.site-settings.update'), $payload);

        $response->assertRedirect(route('admin.site-settings.edit'));
        $response->assertSessionHasErrors('maintenance_message');
    }
}
