<?php

namespace Tests\Feature\Database;

use App\Models\Coupon;
use Database\Seeders\CouponSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CouponSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_coupon_seeder_populates_expected_coupons(): void
    {
        $now = Carbon::create(2024, 1, 15, 12, 0, 0, 'UTC');
        Carbon::setTestNow($now);

        $this->seed(CouponSeeder::class);

        $coupons = Coupon::orderBy('code')->get()->keyBy('code');

        $this->assertCount(4, $coupons);

        $this->assertSame('percentage', $coupons['FLASH50']->type);
        $this->assertSame(50.0, (float) $coupons['FLASH50']->discount);
        $this->assertSame(150.0, (float) $coupons['FLASH50']->minimum_spend);
        $this->assertSame(100, $coupons['FLASH50']->usage_limit);
        $this->assertSame(0, $coupons['FLASH50']->usage_count);
        $this->assertTrue($coupons['FLASH50']->expires_at->lt($now));

        $this->assertSame('fixed', $coupons['FREESHIP']->type);
        $this->assertSame(15.0, (float) $coupons['FREESHIP']->discount);
        $this->assertNull($coupons['FREESHIP']->minimum_spend);
        $this->assertNull($coupons['FREESHIP']->usage_limit);
        $this->assertNull($coupons['FREESHIP']->expires_at);

        $this->assertTrue(
            $coupons['SUMMER25']->expires_at->eq($now->copy()->addMonths(3))
        );
        $this->assertSame(100.0, (float) $coupons['SUMMER25']->minimum_spend);
        $this->assertSame(200, $coupons['SUMMER25']->usage_limit);

        $this->assertTrue(
            $coupons['WELCOME10']->expires_at->eq($now->copy()->addMonths(6))
        );
        $this->assertSame(50.0, (float) $coupons['WELCOME10']->minimum_spend);
        $this->assertSame(500, $coupons['WELCOME10']->usage_limit);
    }

    public function test_coupon_seeder_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 15, 12, 0, 0, 'UTC'));

        $this->seed(CouponSeeder::class);
        $this->seed(CouponSeeder::class);

        $this->assertSame(4, Coupon::count());
    }
}
