<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class ProductToggleTest extends TestCase
{
    use CreatesOfferFixtures;

    use RefreshDatabase;

    public function test_admin_can_toggle_product_active_via_post(): void
    {
        Permission::findOrCreate('manage products', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage products');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $product = $this->createProduct(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('v1.admin.products.toggle-active', $product));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_toggle_active_accepts_form_post_with_csrf_token(): void
    {
        Permission::findOrCreate('manage products', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage products');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $product = $this->createProduct(['is_active' => true]);

        $response = $this->actingAs($admin)
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('v1.admin.products.toggle-active', $product), [
                '_token' => csrf_token(),
            ]);

        $response->assertOk();
    }
}
