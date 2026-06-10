<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_pass_dashboard_permission_check(): void
    {
        Role::findOrCreate('super-admin', 'web');
        Permission::findOrCreate('view dashboard', 'web');

        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('super-admin');

        $this->assertTrue($user->hasRole('super-admin'));
        $this->assertTrue($user->can('view dashboard'));
    }

    public function test_super_admin_passes_admin_panel_role_middleware_roles(): void
    {
        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('admin', 'web');

        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('super-admin');

        $this->assertTrue($user->hasAnyRole(['admin', 'super-admin']));
    }
}
