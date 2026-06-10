<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSettingsLogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_app_logo_and_it_is_publicly_accessible(): void
    {
        Storage::fake('public');

        Role::findOrCreate('super-admin', 'web');

        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('super-admin');

        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $response = $this->actingAs($admin)->post(route('v1.admin.settings.update'), [
            'app_name' => 'Gahez',
            'currency' => 'EGP',
            'app_logo' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $storedPath = Setting::query()->where('key', 'app_logo')->value('value');

        $this->assertNotNull($storedPath);
        Storage::disk('public')->assertExists($storedPath);

        $url = storage_public_url($storedPath);
        $this->assertNotNull($url);
        $this->assertStringContainsString('storage/', $url);

        $this->assertSame($url, brand_logo_url());
    }
}
