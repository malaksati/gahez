<?php

namespace Tests\Feature\Api;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('manage support-chats', 'web');
    }

    public function test_customer_can_list_own_support_chats(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);

        Support::factory()->count(2)->create(['user_id' => $customer->id]);
        Support::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/v1/support-chats');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_customer_cannot_view_another_users_chat(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $other->id]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/support-chats/'.$support->id)
            ->assertForbidden();
    }

    public function test_customer_can_create_support_chat(): void
    {
        $customer = User::factory()->create(['role' => 'user']);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/support-chats', [
            'subject' => 'Order issue',
            'message' => 'My order is late',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.subject', 'Order issue');

        $this->assertDatabaseHas('support_messages', [
            'message' => 'My order is late',
            'sender_id' => $customer->id,
        ]);
    }

    public function test_customer_can_send_message_in_own_chat(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/support-chats/'.$support->id.'/messages', [
            'message' => 'Any update?',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.message', 'Any update?');
    }

    public function test_customer_cannot_message_closed_chat(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->closed()->create(['user_id' => $customer->id]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/support-chats/'.$support->id.'/messages', [
            'message' => 'Hello again',
        ])->assertForbidden();
    }

    public function test_customer_can_paginate_messages(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        for ($i = 0; $i < 3; $i++) {
            $support->messages()->create([
                'sender_type' => 'user',
                'sender_id' => $customer->id,
                'message' => 'Message '.$i,
            ]);
        }

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/v1/support-chats/'.$support->id.'/messages?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_customer_viewing_chat_marks_admin_messages_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        $message = $support->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'message' => 'We are checking your order',
            'read_at' => null,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/support-chats/'.$support->id)
            ->assertOk();

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    public function test_customer_fetching_messages_marks_admin_messages_as_read(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        $message = $support->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'message' => 'Reply from support',
            'read_at' => null,
        ]);

        Sanctum::actingAs($customer);

        $response = $this->getJson('/api/v1/support-chats/'.$support->id.'/messages');

        $response->assertOk()
            ->assertJsonPath('data.0.read_at', fn ($value) => $value !== null)
            ->assertJsonPath('data.0.is_read', true)
            ->assertJsonPath('data.0.read_by_type', 'user');

        $message->refresh();
        $this->assertNotNull($message->read_at);
    }

    public function test_customer_read_does_not_mark_own_messages(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        $message = $support->messages()->create([
            'sender_type' => 'user',
            'sender_id' => $customer->id,
            'message' => 'My question',
            'read_at' => null,
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/support-chats/'.$support->id.'/messages')->assertOk();

        $message->refresh();
        $this->assertNull($message->read_at);
    }
}
