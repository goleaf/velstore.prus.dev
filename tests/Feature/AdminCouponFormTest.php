<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesAdminTestData;
use Tests\TestCase;

class AdminCouponFormTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminTestData;

    protected function setUp(): void
    {
        parent::setUp();

        app()->setLocale('en');

        $admin = \App\Models\User::factory()->create();
        $this->actingAs($admin);

        $this->seedAdminReferenceData();
    }

    public function test_coupon_create_form_includes_enhanced_controls(): void
    {
        $response = $this->get(route('admin.coupons.create'));

        $response->assertOk();

        $response->assertSeeText(__('cms.coupons.form_description'));
        $response->assertSeeText(__('cms.coupons.generate_code'));
        $response->assertSeeText(__('cms.coupons.generate_code_hint'));
        $response->assertSeeText(__('cms.coupons.expiry_section_title'));
        $response->assertSeeText(__('cms.coupons.expiry_toggle_label'));
        $response->assertSeeText(__('cms.coupons.minimum_spend_hint'));
        $response->assertSeeText(__('cms.coupons.usage_limit_hint'));
        $response->assertSeeText(__('cms.coupons.preview.heading'));
        $response->assertSeeText(__('cms.coupons.templates.heading'));
        $response->assertSeeText(__('cms.coupons.templates.flash_sale.label'));
        $response->assertSeeText(__('cms.coupons.helper.heading'));
    }

    public function test_coupon_edit_form_prefills_expiration_value(): void
    {
        $coupon = $this->coupon;
        $expectedValue = $coupon->expires_at
            ? $coupon->expires_at->timezone(config('app.timezone'))->format('Y-m-d\\TH:i')
            : '';

        $response = $this->get(route('admin.coupons.edit', $coupon->id));

        $response->assertOk();
        $response->assertViewIs('admin.coupons.edit');

        if ($expectedValue !== '') {
            $response->assertSee('value="' . $expectedValue . '"', false);
        }

        $response->assertSeeText(__('cms.coupons.expiry_section_description'));
    }
}
