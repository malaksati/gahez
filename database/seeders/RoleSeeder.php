<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            'view dashboard',
            'manage categories',
            'manage products',
            'manage brands',
            'manage branches',
            'manage variants',
            'manage coupons',
            'manage offers',
            'manage goals',
            'manage sliders',
            'manage orders',
            'manage refunds',
            'view reports',
            'manage ratings',
            'manage product-reports',
            'manage tickets',
            'manage support-chats',
            'manage settings',
            'manage admins',
            'manage customers',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Create roles
        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $admin = Role::findOrCreate('admin', 'web');
        Role::findOrCreate('user', 'web');

        // Admin role is a marker; demo admins get permissions in UserSeeder.
        $admin->syncPermissions([]);

        // Super-admin: Gate::before grants all, but sync permissions too for reliability.
        $superAdmin->syncPermissions(Permission::query()->pluck('name')->all());
    }
}
