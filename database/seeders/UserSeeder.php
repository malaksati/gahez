<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $allPermissions = Permission::query()->pluck('name')->all();

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'super-admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'phone' => null,
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->syncRoles(['super-admin']);
        $superAdmin->syncPermissions($allPermissions);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => null,
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['admin']);
        $admin->syncPermissions($allPermissions);

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer1@gmail.com'],
            [
                'name' => 'Customer1',
                'phone' => '50001111',
                'password' => Hash::make('12345678'),
                'role' => 'user',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'birthdate' => '1992-05-20',
                'wallet' => 25,
                'points' => 150,
            ],
        );
        $customer->syncRoles(['user']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
