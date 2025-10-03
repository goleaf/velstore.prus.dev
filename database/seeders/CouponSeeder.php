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
                'expires_at' => $now->copy()->addMonths(6),
            ],
            [
                'code' => 'SUMMER25',
                'discount' => 25,
                'type' => 'fixed',
                'minimum_spend' => 100,
                'usage_limit' => 200,
                'expires_at' => $now->copy()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'discount' => 12,
                'type' => 'fixed',
                'minimum_spend' => 60,
                'usage_limit' => null,
                'expires_at' => $now->copy()->addWeeks(2),
            ],
            [
                'code' => 'FLASH24',
                'discount' => 30,
                'type' => 'percentage',
                'minimum_spend' => 75,
                'usage_limit' => 100,
                'expires_at' => $now->copy()->addDay(),
            ],
            [
                'code' => 'LOYALTY15',
                'discount' => 15,
                'type' => 'percentage',
                'minimum_spend' => 200,
                'usage_limit' => null,
                'expires_at' => null,
            ],
            [
                'code' => 'FLASH50',
                'discount' => 50,
                'type' => 'percentage',
                'minimum_spend' => 150,
                'usage_limit' => 100,
                'expires_at' => $now->copy()->subDay(),
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
                    'usage_count' => 0,
                ]
            );
        }
    }
}
