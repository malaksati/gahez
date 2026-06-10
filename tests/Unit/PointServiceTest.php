<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderItem;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\V1\Services\OrderService;
use App\V1\Services\PointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        setting_forget();
        Setting::query()->updateOrCreate(['key' => 'cashback_percentage'], ['value' => '10', 'type' => 'number']);
        Setting::query()->updateOrCreate(['key' => 'point_to_value'], ['value' => '2', 'type' => 'number']);
    }

    public function test_awards_cashback_when_order_has_no_offers(): void
    {
        $user = User::factory()->create(['role' => 'user', 'wallet' => 0, 'points' => 0]);
        $order = $this->createDeliveredOrder($user, subTotal: 100, orderDiscount: 0);

        $awarded = app(PointService::class)->awardCashbackForDeliveredOrder($order);

        $this->assertTrue($awarded);
        $user->refresh();
        $order->refresh();

        $this->assertSame(5, (int) $user->points);
        $this->assertSame(10.0, (float) $user->wallet);
        $this->assertNotNull($order->cashback_awarded_at);
        $this->assertSame(1, PointTransaction::query()->count());
        $this->assertSame(1, WalletTransaction::query()->count());
    }

    public function test_skips_cashback_when_order_has_offer_discount(): void
    {
        $user = User::factory()->create(['role' => 'user', 'wallet' => 0, 'points' => 0]);
        $order = $this->createDeliveredOrder($user, subTotal: 80, orderDiscount: 20);

        $awarded = app(PointService::class)->awardCashbackForDeliveredOrder($order);

        $this->assertFalse($awarded);
        $user->refresh();
        $this->assertSame(0, (int) $user->points);
        $this->assertSame(0.0, (float) $user->wallet);
    }

    public function test_cashback_is_only_awarded_once(): void
    {
        $user = User::factory()->create(['role' => 'user', 'wallet' => 0, 'points' => 0]);
        $order = $this->createDeliveredOrder($user, subTotal: 100, orderDiscount: 0);
        $service = app(PointService::class);

        $this->assertTrue($service->awardCashbackForDeliveredOrder($order));
        $this->assertFalse($service->awardCashbackForDeliveredOrder($order->fresh()));
    }

    public function test_update_order_status_to_delivered_triggers_cashback(): void
    {
        $user = User::factory()->create(['role' => 'user', 'wallet' => 0, 'points' => 0]);
        $order = $this->createDeliveredOrder($user, subTotal: 50, orderDiscount: 0, status: 'shipped');

        app(OrderService::class)->updateOrderStatus($order, 'delivered');

        $user->refresh();
        $this->assertSame(2, (int) $user->points);
        $this->assertSame(4.0, (float) $user->wallet);
    }

    protected function createDeliveredOrder(User $user, float $subTotal, float $orderDiscount, string $status = 'delivered'): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $this->createBranchId(),
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => $subTotal,
            'order_discount' => $orderDiscount,
            'total' => $subTotal,
            'status' => $status,
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $this->createProductId(),
            'product_name' => 'Item',
            'product_slug' => 'item',
            'product_sku' => 'SKU-1',
            'quantity' => 1,
            'unit_price' => $subTotal,
            'line_discount' => 0,
            'is_gift' => false,
        ]);

        return $order->fresh(['user', 'items']);
    }

    protected function createBranchId(): int
    {
        return Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Test address',
            'is_active' => true,
        ])->id;
    }

    protected function createProductId(): int
    {
        return \App\Models\Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Product', 'ar' => 'منتج'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-CB-'.uniqid(),
            'slug' => 'product-'.uniqid(),
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => \App\Models\Brand::query()->create([
                'name' => ['en' => 'Brand', 'ar' => 'ماركة'],
                'is_active' => true,
            ])->id,
        ])->id;
    }
}
