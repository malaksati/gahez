<?php

namespace App\V1\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AdminUserService
{
    /**
     * Get paginated list of admin users (admin + super-admin roles).
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedAdmins(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::role(['admin', 'super-admin']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->with('roles', 'permissions')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Users that can be promoted to admin (not already admin or super-admin).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function getLinkableUsers()
    {
        return User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', ['admin', 'super-admin']))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);
    }

    /**
     * Create a new admin user with the 'admin' role and given permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $userType = $data['user_type'] ?? 'new';

        if ($userType === 'existing') {
            $user = User::query()->findOrFail($data['user_id']);
            $user->update([
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
            ]);
        }

        $user->assignRole('admin');

        if (! empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return $user;
    }

    /**
     * Update an admin user's info and permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): bool
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Only sync permissions for non-super-admins
        if (! $user->hasRole('super-admin') && isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return true;
    }

    /**
     * Delete an admin user. Cannot delete super-admins.
     */
    public function delete(User $user): bool
    {
        if ($user->hasRole('super-admin')) {
            return false;
        }

        $user->syncPermissions([]);
        $user->removeRole('admin');

        return $user->delete();
    }

    /**
     * Get all available permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPermissions()
    {
        return Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get permissions grouped by module category for the UI.
     *
     * @return array<string, array<string, string>>
     */
    public function getPermissionsGrouped(): array
    {
        return [
            __('messages.Dashboard') => [
                'view dashboard' => __('messages.View dashboard'),
            ],
            __('messages.Catalog') => [
                'manage categories' => __('messages.Manage categories'),
                'manage products' => __('messages.Manage products'),
                'manage brands' => __('messages.Manage brands'),
                'manage branches' => __('messages.Manage branches'),
                'manage variants' => __('messages.Manage variants'),
            ],
            __('messages.Marketing') => [
                'manage coupons' => __('messages.Manage coupons'),
                'manage offers' => __('messages.Manage offers'),
                'manage sliders' => __('messages.Manage sliders'),
            ],
            __('messages.Sales') => [
                'manage orders' => __('messages.Manage orders'),
                'manage refunds' => __('messages.Manage refunds'),
            ],
            __('messages.Analytics') => [
                'view reports' => __('messages.View reports'),
            ],
            __('messages.Rating & Support') => [
                'manage ratings' => __('messages.Manage ratings'),
                'manage product-reports' => __('messages.Manage product reports'),
                'manage tickets' => __('messages.Manage tickets'),
            ],
            __('messages.System') => [
                'manage settings' => __('messages.Manage settings'),
                'manage admins' => __('messages.Manage admin users'),
                'manage customers' => __('messages.Manage customer users'),
            ],
        ];
    }
}
