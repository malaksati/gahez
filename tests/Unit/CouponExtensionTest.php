<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponExtensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_usage_limit_rejects_when_reached(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $coupon = Coupon::query()->create([
            'code' => 'LIMIT10',
            'type' => 'percentage',
            'discount_value' => 10,
            'min_cart_amount' => 0,
            'usage_limit' => 2,
            'usage_limit_per_user' => null,
            'first_order_only' => false,
            'is_active' => true,
        ]);

        $branchId = $this->createBranchId();

        foreach (range(1, 2) as $index) {
            Order::query()->create([
                'user_id' => User::factory()->create(['role' => 'user'])->id,
                'coupon_id' => $coupon->id,
                'branch_id' => $branchId,
                'customer_name' => 'Customer '.$index,
                'customer_email' => 'customer'.$index.'@example.com',
                'sub_total' => 10,
                'total' => 10,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => 'cash_on_delivery',
                'refund_status' => 'none',
            ]);
        }

        $this->assertSame(2, $coupon->totalOrdersUsed());
        $this->assertNotNull($coupon->usabilityErrorForUser($user));
    }

    public function test_free_delivery_coupon_grants_free_delivery_with_no_discount(): void
    {
        $coupon = Coupon::query()->create([
            'code' => 'FREESHIP',
            'type' => 'free_delivery',
            'discount_value' => 0,
            'min_cart_amount' => 0,
            'usage_limit' => null,
            'usage_limit_per_user' => null,
            'first_order_only' => false,
            'is_active' => true,
        ]);

        $this->assertTrue($coupon->grantsFreeDelivery());
        $this->assertSame(0.0, $coupon->calculateDiscount(100));
    }

    public function test_total_usage_limit_allows_when_under_limit(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $coupon = Coupon::query()->create([
            'code' => 'LIMIT100',
            'type' => 'fixed',
            'discount_value' => 5,
            'min_cart_amount' => 0,
            'usage_limit' => 100,
            'usage_limit_per_user' => null,
            'first_order_only' => false,
            'is_active' => true,
        ]);

        $this->assertNull($coupon->usabilityErrorForUser($user));
    }

    protected function createBranchId(): int
    {
        return Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Test address',
            'is_active' => true,
        ])->id;
    }
}
