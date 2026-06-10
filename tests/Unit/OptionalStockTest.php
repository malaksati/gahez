<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\V1\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionalStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_untracked_product_is_available_when_in_stock_flag_is_true(): void
    {
        $product = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Mystery item', 'ar' => 'عنصر'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'MYST-1',
            'slug' => 'mystery-item',
            'price' => 10,
            'stock' => null,
            'is_in_stock' => true,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $this->createBrandId(),
        ]);

        $this->assertFalse($product->tracksStock());
        $this->assertTrue($product->isInStock());
    }

    public function test_delivery_deducts_tracked_stock_and_sets_stock_deducted_at(): void
    {
        $product = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Tracked', 'ar' => 'متابع'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'TRK-1',
            'slug' => 'tracked',
            'price' => 10,
            'stock' => 5,
            'is_in_stock' => true,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $this->createBrandId(),
        ]);

        $user = User::factory()->create(['role' => 'user']);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $this->createBranchId(),
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 10,
            'total' => 10,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Tracked',
            'product_slug' => 'tracked',
            'product_sku' => 'TRK-1',
            'quantity' => 2,
            'unit_price' => 10,
            'line_discount' => 0,
            'is_gift' => false,
        ]);

        app(OrderService::class)->updateOrderStatus($order, 'delivered');

        $product->refresh();
        $order->refresh();

        $this->assertSame(3, $product->stock);
        $this->assertNotNull($order->stock_deducted_at);
    }

    public function test_delivery_skips_untracked_stock_and_stock_deducted_at(): void
    {
        $product = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Untracked', 'ar' => 'غير متابع'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'UNT-1',
            'slug' => 'untracked',
            'price' => 10,
            'stock' => null,
            'is_in_stock' => true,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $this->createBrandId(),
        ]);

        $user = User::factory()->create(['role' => 'user']);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $this->createBranchId(),
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 10,
            'total' => 10,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Untracked',
            'product_slug' => 'untracked',
            'product_sku' => 'UNT-1',
            'quantity' => 2,
            'unit_price' => 10,
            'line_discount' => 0,
            'is_gift' => false,
        ]);

        app(OrderService::class)->updateOrderStatus($order, 'delivered');

        $product->refresh();
        $order->refresh();

        $this->assertNull($product->stock);
        $this->assertNull($order->stock_deducted_at);
    }

    protected function createBrandId(): int
    {
        return \App\Models\Brand::query()->create([
            'name' => ['en' => 'Brand', 'ar' => 'ماركة'],
            'is_active' => true,
        ])->id;
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
