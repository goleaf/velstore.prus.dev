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

        $this->assertCount(8, $coupons);

        $expected = [
            'BULK15' => [
                'type' => 'fixed',
                'discount' => 15.0,
                'minimum_spend' => 250.0,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => null,
            ],
            'FLASH24' => [
                'type' => 'percentage',
                'discount' => 30.0,
                'minimum_spend' => 75.0,
                'usage_limit' => 100,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addDay(),
            ],
            'FLASH50' => [
                'type' => 'percentage',
                'discount' => 50.0,
                'minimum_spend' => 150.0,
                'usage_limit' => 100,
                'usage_count' => 0,
                'expires_at' => $now->copy()->subDay(),
            ],
            'FREESHIP' => [
                'type' => 'fixed',
                'discount' => 12.0,
                'minimum_spend' => 60.0,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addWeeks(2),
            ],
            'LASTCALL' => [
                'type' => 'percentage',
                'discount' => 20.0,
                'minimum_spend' => 80.0,
                'usage_limit' => 75,
                'usage_count' => 10,
                'expires_at' => $now->copy()->addDays(5),
            ],
            'LOYALTY15' => [
                'type' => 'percentage',
                'discount' => 15.0,
                'minimum_spend' => 200.0,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => null,
            ],
            'SUMMER25' => [
                'type' => 'fixed',
                'discount' => 25.0,
                'minimum_spend' => 100.0,
                'usage_limit' => 200,
                'usage_count' => 25,
                'expires_at' => $now->copy()->addMonths(3),
            ],
            'WELCOME10' => [
                'type' => 'percentage',
                'discount' => 10.0,
                'minimum_spend' => 50.0,
                'usage_limit' => 500,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addMonths(6),
            ],
        ];

        foreach ($expected as $code => $data) {
            $coupon = $coupons[$code];

            $this->assertSame($data['type'], $coupon->type, $code.' type mismatch');
            $this->assertSame($data['discount'], (float) $coupon->discount, $code.' discount mismatch');
            $this->assertSame($data['minimum_spend'], $coupon->minimum_spend !== null ? (float) $coupon->minimum_spend : null, $code.' minimum spend mismatch');
            $this->assertSame($data['usage_limit'], $coupon->usage_limit, $code.' usage limit mismatch');
            $this->assertSame($data['usage_count'], $coupon->usage_count, $code.' usage count mismatch');

            if ($data['expires_at'] === null) {
                $this->assertNull($coupon->expires_at, $code.' should not have expiry');
            } else {
                $this->assertTrue($coupon->expires_at->eq($data['expires_at']), $code.' expiry mismatch');
            }
        }
    }

    public function test_coupon_seeder_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 15, 12, 0, 0, 'UTC'));

        $this->seed(CouponSeeder::class);
        $this->seed(CouponSeeder::class);

        $this->assertSame(8, Coupon::count());
    }
}
