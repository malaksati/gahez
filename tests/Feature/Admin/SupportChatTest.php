<?php

namespace Tests\Feature\Admin;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('manage support-chats', 'web');
        Role::findOrCreate('super-admin', 'web');
    }

    public function test_admin_without_permission_cannot_access_support_chats(): void
    {
        Role::findOrCreate('admin', 'web');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('v1.admin.support-chats.index'))
            ->assertForbidden();
    }

    public function test_admin_with_permission_can_view_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');
        $admin->givePermissionTo('manage support-chats');

        Support::factory()->count(2)->create([
            'user_id' => User::factory()->create(['role' => 'user'])->id,
        ]);

        $this->actingAs($admin)
            ->get(route('v1.admin.support-chats.index'))
            ->assertOk()
            ->assertSee(__('messages.Support chats'));
    }

    public function test_admin_can_assign_close_and_reply(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');
        $admin->givePermissionTo('manage support-chats');

        $agent = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $customer->id]);

        $this->actingAs($admin)
            ->put(route('v1.admin.support-chats.assign', $support), [
                'assigned_admin_id' => $agent->id,
            ])
            ->assertRedirect(route('v1.admin.support-chats.show', $support));

        $support->refresh();
        $this->assertSame($agent->id, $support->assigned_admin_id);

        $this->actingAs($admin)
            ->post(route('v1.admin.support-chats.messages.store', $support), [
                'message' => 'Admin reply',
            ])
            ->assertRedirect(route('v1.admin.support-chats.show', $support));

        $this->assertDatabaseHas('support_messages', [
            'support_id' => $support->id,
            'message' => 'Admin reply',
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->put(route('v1.admin.support-chats.status.update', $support), [
                'status' => 'closed',
            ])
            ->assertRedirect(route('v1.admin.support-chats.show', $support));

        $support->refresh();
        $this->assertSame('closed', $support->status);
        $this->assertNotNull($support->closed_at);
    }
}
