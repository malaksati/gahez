<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer1@gmail.com')->first();
        $address = $customer
            ? Address::query()->where('user_id', $customer->id)->where('is_default', true)->first()
            : null;
        $apples = Product::query()->where('sku', 'APPLE-1KG')->first();
        $milk = Product::query()->where('sku', 'MILK-1L')->first();
        $coupon = Coupon::query()->where('code', 'WELCOME10')->first();

        if (! $customer || ! $address || ! $apples || ! $milk) {
            return;
        }

        $subTotal = 4.45;
        $couponDiscount = 0.45;
        $shipping = 1.50;
        $total = 5.50;

        $order = Order::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'notes' => 'Demo seeded order',
            ],
            [
                'branch_id' => 1,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $address->phone ?? $customer->phone,
                'sub_total' => $subTotal,
                'order_discount' => 0,
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $couponDiscount,
                'total_shipping' => $shipping,
                'wallet_used' => 0,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash_on_delivery',
                'refund_status' => 'none',
                'address_id' => $address->id,
                'shipping_address_snapshot' => [
                    'name' => $address->name,
                    'address' => $address->address,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'phone' => $address->phone,
                ],
                'total_commission' => 0,
            ],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $apples->id, 'variant_id' => null],
            ['quantity' => 2, 'unit_price' => 1.75, 'note' => 'Red apples please', 'line_discount' => 0, 'is_gift' => false],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $milk->id, 'variant_id' => null],
            ['quantity' => 1, 'unit_price' => 0.95, 'note' => 'Cold milk please', 'line_discount' => 0, 'is_gift' => false],
        );

        $deliveredOrder = Order::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'notes' => 'Demo delivered order',
            ],
            [
                'branch_id' => 1,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $address->phone ?? $customer->phone,
                'sub_total' => 1.75,
                'order_discount' => 0,
                'coupon_id' => null,
                'coupon_discount' => 0,
                'total_shipping' => 1.50,
                'wallet_used' => 0,
                'total' => 3.25,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => 'cash_on_delivery',
                'paid_at' => now()->subDays(3),
                'refund_status' => 'none',
                'address_id' => $address->id,
                'shipping_address_snapshot' => [
                    'name' => $address->name,
                    'address' => $address->address,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'phone' => $address->phone,
                ],
                'total_commission' => 0,
            ],
        );

        OrderItem::query()->updateOrCreate(
            ['order_id' => $deliveredOrder->id, 'product_id' => $apples->id, 'variant_id' => null],
            ['quantity' => 1, 'unit_price' => 1.75, 'note' => 'Red apples please', 'line_discount' => 0, 'is_gift' => false],
        );
    }
}
