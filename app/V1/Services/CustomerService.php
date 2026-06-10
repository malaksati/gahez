<?php

namespace App\V1\Services;

use App\Models\Address;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\V1\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
    public function create(array $data, ?UploadedFile $image = null): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_verified' => (bool) ($data['is_verified'] ?? true),
            'wallet' => round((float) ($data['wallet'] ?? 0), 2),
            'points' => (int) ($data['points'] ?? 0),
            'image' => $this->storeUploadedImage($image),
            'email_verified_at' => ! empty($data['email']) ? now() : null,
            'phone_verified_at' => ! empty($data['phone']) ? now() : null,
        ]);

        $user->assignRole('user');

        if (! empty($data['address']) && $this->addressPayloadIsComplete($data['address'])) {
            $this->syncPrimaryAddress($user->id, $data['address'], forceDefault: true);
        }

        return $user;
    }

    /**
     * Update a customer's info.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?UploadedFile $image = null, bool $removeImage = false): bool
    {
        return DB::transaction(function () use ($user, $data, $image, $removeImage) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

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
                $updateData['is_active'] = (bool) $data['is_active'];
            }

            if (isset($data['is_verified'])) {
                $updateData['is_verified'] = (bool) $data['is_verified'];
            }

            if ($removeImage) {
                $this->deleteStoredImage($lockedUser);
                $updateData['image'] = null;
            } elseif ($image instanceof UploadedFile && $image->isValid()) {
                $this->deleteStoredImage($lockedUser);
                $updateData['image'] = UploadStorage::store($image, 'avatars', 'public');
            }

            if (array_key_exists('wallet', $data)) {
                $newWallet = round((float) $data['wallet'], 2);
                $oldWallet = round((float) $lockedUser->wallet, 2);

                if ($newWallet !== $oldWallet) {
                    $diff = round($newWallet - $oldWallet, 2);
                    WalletTransaction::query()->create([
                        'user_id' => $lockedUser->id,
                        'type' => $diff >= 0 ? 'addition' : 'subtraction',
                        'amount' => abs($diff),
                        'balance_after' => $newWallet,
                        'notes' => __('messages.Admin adjustment'),
                    ]);
                    $updateData['wallet'] = $newWallet;
                }
            }

            if (array_key_exists('points', $data)) {
                $newPoints = (int) $data['points'];
                $oldPoints = (int) $lockedUser->points;

                if ($newPoints !== $oldPoints) {
                    $diff = $newPoints - $oldPoints;
                    PointTransaction::query()->create([
                        'user_id' => $lockedUser->id,
                        'type' => $diff >= 0 ? 'addition' : 'subtraction',
                        'amount' => abs($diff),
                        'balance_after' => $newPoints,
                        'notes' => __('messages.Admin adjustment'),
                    ]);
                    $updateData['points'] = $newPoints;
                }
            }

            $userUpdated = $lockedUser->update($updateData);

            $addressSynced = false;

            if (! empty($data['address']) && $this->addressPayloadIsComplete($data['address'])) {
                $this->syncPrimaryAddress($lockedUser->id, $data['address']);
                $addressSynced = true;
            }

            return $userUpdated || $addressSynced;
        });
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

    /**
     * @param  array<string, mixed>  $addressData
     */
    protected function syncPrimaryAddress(int $userId, array $addressData, bool $forceDefault = false): void
    {
        $payload = [
            'name' => $addressData['name'],
            'address' => $addressData['address'],
            'latitude' => $addressData['latitude'],
            'longitude' => $addressData['longitude'],
            'phone' => $addressData['phone'] ?? null,
            'city' => $addressData['city'] ?? null,
            'state' => $addressData['state'] ?? null,
            'is_default' => $forceDefault || (bool) ($addressData['is_default'] ?? false),
            'is_active' => true,
        ];

        $addressId = $addressData['id'] ?? null;
        $address = null;

        if ($addressId) {
            $address = Address::query()
                ->where('user_id', $userId)
                ->where('id', $addressId)
                ->first();
        }

        if ($address) {
            $address->update($payload);
        } else {
            $address = Address::query()->create(array_merge($payload, ['user_id' => $userId]));
        }

        if ($payload['is_default']) {
            Address::query()
                ->where('user_id', $userId)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }
    }

    /**
     * @param  array<string, mixed>  $addressData
     */
    protected function addressPayloadIsComplete(array $addressData): bool
    {
        return filled($addressData['name'])
            && filled($addressData['address'])
            && filled($addressData['latitude'])
            && filled($addressData['longitude']);
    }

    protected function storeUploadedImage(?UploadedFile $image): ?string
    {
        if (! $image instanceof UploadedFile || ! $image->isValid()) {
            return null;
        }

        return UploadStorage::store($image, 'avatars', 'public');
    }

    protected function deleteStoredImage(User $user): void
    {
        $path = $user->getRawOriginal('image');

        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
