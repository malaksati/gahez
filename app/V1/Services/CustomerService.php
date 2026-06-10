<?php

namespace App\V1\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Get paginated list of customers.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCustomers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = User::where('role', 'user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->with('roles', 'permissions')
            ->withCount('orders')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user', // Default customer role string
            'is_active' => true,
            'is_verified' => true,
            'email_verified_at' => !empty($data['email']) ? now() : null,
            'phone_verified_at' => !empty($data['phone']) ? now() : null,
        ]);

        $user->assignRole('user');

        return $user;
    }

    /**
     * Update a customer's info.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): bool
    {
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }

        return $user->update($updateData);
    }

    /**
     * Delete a customer.
     */
    public function delete(User $user): bool
    {
        $user->syncPermissions([]);
        $user->removeRole('user');

        return $user->delete();
    }
}
