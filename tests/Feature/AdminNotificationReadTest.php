<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminNotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_notification_as_read_via_json(): void
    {
        Role::findOrCreate('admin', 'web');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\NewOrderForAdminNotification',
            'data' => [
                'title' => 'New order',
                'message' => 'Order #1',
                'url' => '/admin/orders/1',
            ],
        ]);

        $notification = $admin->notifications()->first();

        $this->actingAs($admin)
            ->postJson(route('v1.admin.notifications.read', $notification->id))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_all_notifications_as_read_from_index(): void
    {
        Role::findOrCreate('admin', 'web');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');

        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\NewOrderForAdminNotification',
            'data' => ['title' => 'One', 'message' => 'First'],
        ]);
        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\NewOrderForAdminNotification',
            'data' => ['title' => 'Two', 'message' => 'Second'],
        ]);

        $this->actingAs($admin)
            ->post(route('v1.admin.notifications.mark-all-read'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $admin->unreadNotifications()->count());
    }
}
