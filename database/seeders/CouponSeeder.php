<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $coupons = [
            [
                'code' => 'WELCOME10',
                'discount' => 10,
                'type' => 'percentage',
                'minimum_spend' => 50,
                'usage_limit' => 500,
                'usage_count' => 0,
                'expires_at' => $now->copy()->addMonths(6),
            ],
            [
                'code' => 'SUMMER25',
                'discount' => 25,
                'type' => 'fixed',
                'minimum_spend' => 100,
                'usage_limit' => 200,
                'usage_count' => 25,
                'expires_at' => $now->copy()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'discount' => 15,
                'type' => 'fixed',
                'minimum_spend' => null,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => null,
            ],
            [
                'code' => 'FLASH50',
                'discount' => 50,
                'type' => 'percentage',
                'minimum_spend' => 150,
                'usage_limit' => 100,
                'usage_count' => 0,
                'expires_at' => $now->copy()->subDay(),
            ],
            [
                'code' => 'LASTCALL',
                'discount' => 20,
                'type' => 'percentage',
                'minimum_spend' => 80,
                'usage_limit' => 75,
                'usage_count' => 10,
                'expires_at' => $now->copy()->addDays(5),
            ],
            [
                'code' => 'BULK15',
                'discount' => 15,
                'type' => 'fixed',
                'minimum_spend' => 250,
                'usage_limit' => null,
                'usage_count' => 0,
                'expires_at' => null,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                [
                    'discount' => $coupon['discount'],
                    'type' => $coupon['type'],
                    'minimum_spend' => $coupon['minimum_spend'],
                    'usage_limit' => $coupon['usage_limit'],
                    'expires_at' => $coupon['expires_at'],
                    'usage_count' => $coupon['usage_count'] ?? 0,
                ]
            );
        }
    }
}
