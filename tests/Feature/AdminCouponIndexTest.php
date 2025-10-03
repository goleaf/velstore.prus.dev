<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Feature\Concerns\CreatesAdminTestData;
use Tests\TestCase;

class AdminCouponIndexTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAdminTestData;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2024, 1, 15, 12, 0, 0, 'UTC'));

        app()->setLocale('en');

        $admin = User::factory()->create();
        $this->actingAs($admin);

        $this->seedAdminReferenceData();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_index_page_displays_enhanced_filters(): void
    {
        Coupon::factory()->create();

        $response = $this->get(route('admin.coupons.index'));

        $response->assertOk();
        $response->assertSeeText(__('cms.coupons.filters.search_label'));
        $response->assertSeeText(__('cms.coupons.filters.type.label'));
        $response->assertSeeText(__('cms.coupons.filters.usage.label'));
        $response->assertSeeText(__('cms.coupons.filters.expiring_soon'));
    }

    public function test_search_filter_restricts_results(): void
    {
        $matching = Coupon::factory()->create(['code' => 'SAVE2024']);
        $nonMatching = Coupon::factory()->create(['code' => 'HELLO2024']);

        $response = $this->get(route('admin.coupons.index', ['search' => 'SAVE']));

        $response->assertOk();
        $response->assertSeeText($matching->code);
        $response->assertDontSeeText($nonMatching->code);
    }

    public function test_type_and_usage_filters_can_be_combined(): void
    {
        Coupon::factory()->create(['code' => 'PCT100', 'type' => 'percentage', 'usage_limit' => 100]);
        Coupon::factory()->create(['code' => 'FIXED10', 'type' => 'fixed', 'usage_limit' => null]);

        $response = $this->get(route('admin.coupons.index', ['type' => 'fixed', 'usage' => 'unlimited']));

        $response->assertOk();
        $response->assertSeeText('FIXED10');
        $response->assertDontSeeText('PCT100');
    }

    public function test_expiring_soon_status_filter_only_shows_upcoming_expiries(): void
    {
        $soon = Coupon::factory()->create(['code' => 'SOON7', 'expires_at' => Carbon::now()->addDays(3)]);
        $later = Coupon::factory()->create(['code' => 'LATER30', 'expires_at' => Carbon::now()->addMonths(2)]);
        $expired = Coupon::factory()->create(['code' => 'EXPIRED', 'expires_at' => Carbon::now()->subDay()]);

        $response = $this->get(route('admin.coupons.index', ['status' => 'expiring_soon']));

        $response->assertOk();
        $response->assertSeeText($soon->code);
        $response->assertDontSeeText($later->code);
        $response->assertDontSeeText($expired->code);
    }

    public function test_stats_include_expiring_and_unlimited_counts(): void
    {
        Coupon::factory()->create(['usage_limit' => null]);
        Coupon::factory()->create(['usage_limit' => 10, 'expires_at' => Carbon::now()->addDays(2)]);

        $response = $this->get(route('admin.coupons.index'));

        $response->assertOk();
        $response->assertSeeText(__('cms.coupons.stats.expiring_soon'));
        $response->assertSeeText(__('cms.coupons.stats.unlimited'));
    }
}
