<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusUpdatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_includes_database_and_mail_when_email_present(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'phone' => '50001111',
            'phone_verified_at' => now(),
            'email' => 'customer@example.com',
        ]);

        $order = $this->createOrder($user, 'shipped');
        $notification = new OrderStatusUpdatedNotification($order);

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
    }

    public function test_notification_implements_should_queue(): void
    {
        $user = User::factory()->create(['role' => 'user', 'phone' => '50001111']);
        $order = $this->createOrder($user);
        $notification = new OrderStatusUpdatedNotification($order);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $notification);
    }

    protected function createOrder(User $user, string $status = 'pending'): Order
    {
        return Order::query()->create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? '50001111',
            'sub_total' => 10,
            'total_shipping' => 0,
            'total' => 10,
            'status' => $status,
            'payment_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
        ]);
    }
}
