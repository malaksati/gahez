<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderRatingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_rate_order_before_it_is_delivered(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = $this->createOrderForUser($user, 'pending');

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/rate", [
            'rating' => 5,
            'comment' => 'Great',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('messages.You can only rate an order after it is delivered.'),
            ]);

        $this->assertDatabaseCount('order_ratings', 0);
    }

    public function test_can_rate_delivered_order(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = $this->createOrderForUser($user, 'delivered');

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/rate", [
            'rating' => 4,
            'comment' => 'Good service',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => __('messages.Order rated successfully.'),
            ]);

        $this->assertDatabaseHas('order_ratings', [
            'order_id' => $order->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);
    }

    public function test_cannot_rate_another_users_order(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $order = $this->createOrderForUser($owner, 'delivered');

        Sanctum::actingAs($other);

        $response = $this->postJson("/api/v1/orders/{$order->id}/rate", [
            'rating' => 5,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => __('messages.Order not found in your account.'),
            ]);
    }

    public function test_cannot_rate_order_twice(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = $this->createOrderForUser($user, 'delivered');

        OrderRating::query()->create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/orders/{$order->id}/rate", [
            'rating' => 3,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('messages.You have already rated this order.'),
            ]);
    }

    protected function createOrderForUser(User $user, string $status): Order
    {
        return Order::query()->create([
            'user_id' => $user->id,
            'branch_id' => Branch::query()->create([
                'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
                'address' => 'Branch',
                'is_active' => true,
            ])->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'sub_total' => 100,
            'total' => 100,
            'status' => $status,
            'payment_status' => 'paid',
            'payment_method' => 'cash_on_delivery',
            'refund_status' => 'none',
        ]);
    }
}
