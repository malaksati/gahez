<?php

namespace App\V1\Services;

use App\Models\Address;
use App\V1\Repositories\AddressRepository;
use Illuminate\Database\Eloquent\Collection;

class AddressService
{
    public function __construct(
        protected AddressRepository $addresses,
    ) {}

    public function allByUser(int $userId): Collection
    {
        return $this->addresses->allByUser($userId);
    }

    public function findById(int $id, int $userId): Address
    {
        return $this->addresses->findById($id, $userId);
    }

    public function create(int $userId, array $data): Address
    {
        return $this->addresses->create(array_merge($data, ['user_id' => $userId]));
    }

    public function update(int $id, int $userId, array $data): Address
    {
        $address = $this->addresses->findById($id, $userId);

        return $this->addresses->update($address, $data);
    }

    public function delete(int $id, int $userId): bool
    {
        $address = $this->addresses->findById($id, $userId);

        return $this->addresses->delete($address);
    }
    public function forceDelete(int $id, int $userId): bool
    {
        $address = $this->addresses->findById($id, $userId);

        return $this->addresses->forceDelete($address);
    }
    public function restore(int $id, int $userId): bool
    {
        $address = $this->addresses->findById($id, $userId);

        return $this->addresses->restore($address);
    }
    public function search(string $search): Collection
    {
        return $this->addresses->search($search);
    }

}
