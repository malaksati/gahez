<?php

namespace Tests\Unit;

use App\Events\SupportMessageSent;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportMessageFromAdminNotification;
use App\Notifications\SupportMessageFromCustomerNotification;
use App\V1\Services\SupportChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SupportChatService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupportChatService::class);
        Permission::findOrCreate('manage support-chats', 'web');
    }

    public function test_create_conversation_with_initial_message(): void
    {
        Event::fake([SupportMessageSent::class]);
        Notification::fake();

        Role::findOrCreate('admin', 'web');

        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        $admin->givePermissionTo('manage support-chats');

        $support = $this->service->createConversation($customer->id, [
            'subject' => 'Help needed',
            'message' => 'Hello support',
        ]);

        $this->assertDatabaseHas('supports', [
            'id' => $support->id,
            'user_id' => $customer->id,
            'status' => 'open',
            'subject' => 'Help needed',
        ]);
        $this->assertDatabaseHas('support_messages', [
            'support_id' => $support->id,
            'message' => 'Hello support',
            'sender_type' => 'user',
            'sender_id' => $customer->id,
        ]);

        Event::assertDispatched(SupportMessageSent::class);
        Notification::assertSentTo($admin, SupportMessageFromCustomerNotification::class);
    }

    public function test_admin_message_notifies_customer_and_auto_assigns(): void
    {
        Event::fake([SupportMessageSent::class]);
        Notification::fake();

        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $support = Support::factory()->create([
            'user_id' => $customer->id,
            'assigned_admin_id' => null,
        ]);

        $message = $this->service->addMessage($support->id, [
            'message' => 'We can help',
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
        ]);

        $support->refresh();
        $this->assertSame($admin->id, $support->assigned_admin_id);
        $this->assertSame('We can help', $message->message);

        Notification::assertSentTo($customer, SupportMessageFromAdminNotification::class);
        Event::assertDispatched(SupportMessageSent::class);
    }

    public function test_cannot_add_message_to_closed_chat(): void
    {
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->closed()->create(['user_id' => $customer->id]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->addMessage($support->id, [
            'message' => 'Still need help',
            'sender_type' => 'user',
            'sender_id' => $customer->id,
        ]);
    }

    public function test_customer_message_notifies_assigned_admin(): void
    {
        Notification::fake();

        $customer = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $support = Support::factory()->create([
            'user_id' => $customer->id,
            'assigned_admin_id' => $admin->id,
        ]);

        $this->service->addMessage($support->id, [
            'message' => 'Follow up',
            'sender_type' => 'user',
            'sender_id' => $customer->id,
        ]);

        Notification::assertSentTo($admin, SupportMessageFromCustomerNotification::class);
    }
}
