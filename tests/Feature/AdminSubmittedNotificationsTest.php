<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderRefundRequestSubmittedAdminNotification;
use App\Notifications\ProductReportSubmittedAdminNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class AdminSubmittedNotificationsTest extends TestCase
{
    use CreatesOfferFixtures;
    use RefreshDatabase;

    public function test_product_report_notifies_admins_with_permission(): void
    {
        Notification::fake();

        [$customer, $admin] = $this->createCustomerAndAdminWithPermissions(['manage product-reports']);
        $product = $this->createProduct();

        Sanctum::actingAs($customer);

        $this->postJson("/api/v1/products/{$product->id}/report", [
            'reason' => 'Wrong price',
            'description' => 'Price on shelf differs',
        ])->assertOk();

        Notification::assertSentTo(
            $admin,
            ProductReportSubmittedAdminNotification::class,
            fn (ProductReportSubmittedAdminNotification $notification) => $notification->report->product_id === $product->id,
        );
    }

    public function test_refund_request_notifies_admins_with_permission(): void
    {
        Notification::fake();

        [$customer, $admin] = $this->createCustomerAndAdminWithPermissions(['manage refunds']);
        $order = $this->createShippedOrderForUser($customer);

        Sanctum::actingAs($customer);

        $this->postJson("/api/v1/orders/{$order->id}/refund-request", [
            'reason' => 'Damaged item',
            'details' => 'Box was open',
        ])->assertCreated();

        Notification::assertSentTo(
            $admin,
            OrderRefundRequestSubmittedAdminNotification::class,
            fn (OrderRefundRequestSubmittedAdminNotification $notification) => $notification->refundRequest->order_id === $order->id,
        );
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: User, 1: User}
     */
    protected function createCustomerAndAdminWithPermissions(array $permissions): array
    {
        Role::findOrCreate('admin', 'web');

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $customer = $this->createUser();
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $admin->givePermissionTo($permissions);

        return [$customer, $admin];
    }

    protected function createShippedOrderForUser(User $user): Order
    {
        $branch = Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Branch address',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone,
            'sub_total' => 50,
            'total' => 50,
            'total_shipping' => 0,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);
    }
}
