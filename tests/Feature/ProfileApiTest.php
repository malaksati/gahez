<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_show_includes_birthdate(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'birthdate' => '1995-03-15',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('data.user.birthdate', '1995-03-15');
    }

    public function test_profile_update_persists_birthdate(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'birthdate' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/profile', [
            'birthdate' => '1990-07-20',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.birthdate', '1990-07-20');

        $user->refresh();
        $this->assertSame('1990-07-20', $user->birthdate?->toDateString());
    }

    public function test_profile_update_can_clear_birthdate(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'birthdate' => '1990-07-20',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/profile', [
            'birthdate' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.birthdate', null);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'birthdate' => null,
        ]);
    }

    public function test_register_accepts_optional_birthdate(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Birthday User',
            'email' => 'birthday@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'birthdate' => '1988-12-01',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.birthdate', '1988-12-01');

        $registeredUser = User::query()->where('email', 'birthday@example.com')->first();
        $this->assertSame('1988-12-01', $registeredUser?->birthdate?->toDateString());
    }

    public function test_register_normalizes_local_phone_to_plus_twenty_format(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Phone User',
            'phone' => '01012345678',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'phone' => '+201012345678',
        ]);
    }

    public function test_register_rejects_invalid_egypt_phone(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Phone User',
            'phone' => '50001111',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_profile_update_normalizes_phone_to_plus_twenty_format(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'phone' => '+201000111111',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/profile', [
            'phone' => '01098765432',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('+201098765432', $user->phone);
    }
}
