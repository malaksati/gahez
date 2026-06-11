<?php

namespace Tests\Feature\Broadcast;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SupportChannelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('manage support-chats', 'web');

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => 'test-app',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
            'broadcasting.connections.pusher.options.host' => '127.0.0.1',
            'broadcasting.connections.pusher.options.port' => 6001,
            'broadcasting.connections.pusher.options.scheme' => 'http',
            'broadcasting.connections.pusher.options.useTLS' => false,
        ]);
    }

    public function test_stranger_cannot_authorize_support_channel(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $support = Support::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($stranger, 'sanctum');

        $this->postJson('/api/v1/broadcasting/auth', [
            'channel_name' => 'private-support.'.$support->id,
            'socket_id' => '1.1',
        ])->assertForbidden();
    }

    public function test_support_channel_access_matches_view_policy(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->givePermissionTo('manage support-chats');

        $support = Support::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue(Gate::forUser($owner)->allows('view', $support));
        $this->assertTrue(Gate::forUser($admin)->allows('view', $support));
        $this->assertFalse(Gate::forUser($stranger)->allows('view', $support));
    }
}
