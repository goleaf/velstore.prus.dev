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
                'expires_at' => $now->copy()->addMonths(6),
            ],
            [
                'code' => 'SUMMER25',
                'discount' => 25,
                'type' => 'fixed',
                'expires_at' => $now->copy()->addMonths(3),
            ],
            [
                'code' => 'FREESHIP',
                'discount' => 15,
                'type' => 'fixed',
                'expires_at' => null,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(
                ['code' => $coupon['code']],
                [
                    'discount' => $coupon['discount'],
                    'type' => $coupon['type'],
                    'expires_at' => $coupon['expires_at'],
                ]
            );
        }
    }
}
