<?php

namespace Tests\Unit;

use App\Models\User;
use App\V1\Services\AdminUserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('super-admin', 'web');
        Permission::findOrCreate('view dashboard', 'web');
    }

    public function test_create_promotes_existing_user_to_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'email' => 'customer@example.com',
        ]);

        $admin = app(AdminUserService::class)->create([
            'user_type' => 'existing',
            'user_id' => $user->id,
            'permissions' => ['view dashboard'],
        ]);

        $this->assertSame($user->id, $admin->id);
        $this->assertTrue($admin->fresh()->hasRole('admin'));
        $this->assertTrue($admin->fresh()->hasPermissionTo('view dashboard'));
    }

    public function test_create_new_user_with_admin_role(): void
    {
        $admin = app(AdminUserService::class)->create([
            'user_type' => 'new',
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
            'permissions' => ['view dashboard'],
        ]);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertSame('admin', $admin->role);
    }
}
