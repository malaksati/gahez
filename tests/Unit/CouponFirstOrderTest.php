<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponFirstOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_order_only_coupon_rejects_user_with_previous_order(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $coupon = Coupon::query()->create([
            'code' => 'NEW50',
            'type' => 'percentage',
            'discount_value' => 50,
            'min_cart_amount' => 0,
            'usage_limit_per_user' => null,
            'first_order_only' => true,
            'is_active' => true,
        ]);

        $branchId = Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Test address',
            'is_active' => true,
        ])->id;

        Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 10,
            'total' => 10,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        $this->assertNotNull($coupon->usabilityErrorForUser($user));
    }

    public function test_first_order_only_coupon_allows_new_customer(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $coupon = Coupon::query()->create([
            'code' => 'NEW50',
            'type' => 'percentage',
            'discount_value' => 50,
            'min_cart_amount' => 0,
            'usage_limit_per_user' => null,
            'first_order_only' => true,
            'is_active' => true,
        ]);

        $this->assertNull($coupon->usabilityErrorForUser($user));
    }
}
