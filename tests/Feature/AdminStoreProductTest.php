<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class AdminStoreProductTest extends TestCase
{
    use CreatesOfferFixtures;
    use RefreshDatabase;

    public function test_admin_can_store_simple_product_with_image_uploads(): void
    {
        Permission::findOrCreate('manage products', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage products');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $brand = $this->createBrand();
        $unitId = Unit::query()->where('code', 'piece')->value('id');

        if (! $unitId) {
            $unitId = Unit::query()->create([
                'code' => 'piece',
                'name' => ['en' => 'piece', 'ar' => 'قطعة'],
                'is_active' => true,
            ])->id;
        }

        $response = $this->actingAs($admin)->post(route('v1.admin.products.store'), [
            '_token' => csrf_token(),
            'type' => 'simple',
            'brand_id' => $brand->id,
            'name' => ['en' => 'Test product', 'ar' => 'منتج تجريبي'],
            'description' => ['en' => 'Description', 'ar' => 'وصف'],
            'sku' => 'PRD-STORE-TEST',
            'is_in_stock' => 1,
            'is_active' => 1,
            'is_approved' => 1,
            'discount' => 0,
            'discount_type' => 'percentage',
            'product_units' => [
                [
                    'unit_id' => $unitId,
                    'price' => 12.5,
                    'stock' => 10,
                    'factor' => 1,
                    'discount' => 0,
                    'discount_type' => 'percentage',
                    'is_default' => 1,
                    'is_active' => 1,
                    'is_in_stock' => 1,
                ],
            ],
            'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
            'images' => [
                UploadedFile::fake()->image('gallery-0.jpg'),
                UploadedFile::fake()->image('gallery-1.jpg'),
            ],
        ]);

        $response->assertRedirect(route('v1.admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-STORE-TEST',
            'brand_id' => $brand->id,
        ]);
    }

    public function test_admin_can_store_product_with_octet_stream_image_uploads(): void
    {
        Permission::findOrCreate('manage products', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage products');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $brand = $this->createBrand();
        $unitId = Unit::query()->where('code', 'piece')->value('id');

        if (! $unitId) {
            $unitId = Unit::query()->create([
                'code' => 'piece',
                'name' => ['en' => 'piece', 'ar' => 'قطعة'],
                'is_active' => true,
            ])->id;
        }

        $thumbSource = UploadedFile::fake()->image('thumb.jpg');
        $gallery0Source = UploadedFile::fake()->image('gallery-0.jpg');
        $gallery1Source = UploadedFile::fake()->image('gallery-1.jpg');

        $thumbPath = tempnam(sys_get_temp_dir(), 'img');
        $gallery0Path = tempnam(sys_get_temp_dir(), 'img');
        $gallery1Path = tempnam(sys_get_temp_dir(), 'img');

        file_put_contents($thumbPath, file_get_contents($thumbSource->getRealPath()));
        file_put_contents($gallery0Path, file_get_contents($gallery0Source->getRealPath()));
        file_put_contents($gallery1Path, file_get_contents($gallery1Source->getRealPath()));

        $response = $this->actingAs($admin)->post(route('v1.admin.products.store'), [
            '_token' => csrf_token(),
            'type' => 'simple',
            'brand_id' => $brand->id,
            'name' => ['en' => 'Octet stream product', 'ar' => 'منتج'],
            'description' => ['en' => 'Description', 'ar' => 'وصف'],
            'sku' => 'PRD-OCTET-TEST',
            'is_in_stock' => 1,
            'is_active' => 1,
            'is_approved' => 1,
            'discount' => 0,
            'discount_type' => 'percentage',
            'product_units' => [
                [
                    'unit_id' => $unitId,
                    'price' => 12.5,
                    'stock' => 10,
                    'factor' => 1,
                    'discount' => 0,
                    'discount_type' => 'percentage',
                    'is_default' => 1,
                    'is_active' => 1,
                    'is_in_stock' => 1,
                ],
            ],
            'thumbnail' => new UploadedFile($thumbPath, 'thumb.jpg', 'application/octet-stream', null, true),
            'images' => [
                new UploadedFile($gallery0Path, 'gallery-0.jpg', 'application/octet-stream', null, true),
                new UploadedFile($gallery1Path, 'gallery-1.jpg', 'application/octet-stream', null, true),
            ],
        ]);

        $response->assertRedirect(route('v1.admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => 'PRD-OCTET-TEST',
        ]);
    }

    public function test_admin_can_quick_create_catalog_unit_from_product_wizard(): void
    {
        Permission::findOrCreate('manage products', 'web');
        $role = Role::findOrCreate('super-admin', 'web');
        $role->givePermissionTo('manage products');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->postJson(route('v1.admin.products.quick-catalog-unit'), [
            'name' => [
                'en' => 'Crate',
                'ar' => 'صندوق',
            ],
            'code' => 'crate',
        ]);

        $response->assertOk()
            ->assertJsonPath('unit.code', 'crate')
            ->assertJsonPath('unit.name_en', 'Crate')
            ->assertJsonPath('unit.name_ar', 'صندوق');

        $this->assertDatabaseHas('units', [
            'code' => 'crate',
        ]);
    }
}
