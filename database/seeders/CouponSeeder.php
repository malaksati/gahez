<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'discount_value' => 10,
                'min_cart_amount' => 5,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 1,
                'first_order_only' => true,
            ],
            [
                'code' => 'SAVE5',
                'type' => 'fixed',
                'discount_value' => 5,
                'min_cart_amount' => 20,
                'usage_limit' => 500,
                'usage_limit_per_user' => 3,
                'first_order_only' => false,
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'free_delivery',
                'discount_value' => 0,
                'min_cart_amount' => 15,
                'usage_limit' => null,
                'usage_limit_per_user' => null,
                'first_order_only' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::query()->updateOrCreate(
                ['code' => $coupon['code']],
                array_merge($coupon, [
                    'start_date' => now()->subDay(),
                    'end_date' => now()->addMonths(6),
                    'is_active' => true,
                ]),
            );
        }
    }
}
