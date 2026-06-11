<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\Concerns\BuildsRealisticOrders;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use BuildsRealisticOrders;

    public function run(): void
    {
        $customer = User::query()->where('email', 'customer1@gmail.com')->first();
        $address = $customer
            ? Address::query()->where('user_id', $customer->id)->where('is_default', true)->first()
            : null;

        if (! $customer || ! $address) {
            return;
        }

        $welcomeCoupon = Coupon::query()->where('code', 'WELCOME10')->first();

        // First valid order (delivered): uses first-order coupon, meets cart limits from settings.
        $this->seedOrderForCustomer(
            $customer,
            $address,
            'Demo delivered order',
            [
                'APPLE-1KG' => 8,
                'MILK-1L' => 10,
                'BREAD-500G' => 10,
                'JUICE-1L' => 2,
                'CHIPS-200G' => 3,
            ],
            [
                'status' => 'delivered',
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(3),
            ],
            $welcomeCoupon,
        );

        // Current pending order: second valid order, no first-order coupon.
        $this->seedOrderForCustomer(
            $customer,
            $address,
            'Demo seeded order',
            [
                'WATER-1.5L' => 30,
                'DETERGENT-1L' => 10,
                'TOMATO-1KG' => 10,
                'JUICE-1L' => 5,
                'BREAD-500G' => 5,
            ],
            [
                'status' => 'pending',
                'payment_status' => 'pending',
                'paid_at' => null,
            ],
            Coupon::query()->where('code', 'SAVE5')->first(),
        );
    }
}
