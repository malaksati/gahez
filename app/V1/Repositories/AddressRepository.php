<?php

namespace App\V1\Repositories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AddressRepository
{
    protected $model;

    public function __construct(Address $address)
    {
        $this->model = $address;
    }

    public function allByUser(int $userId): Collection
    {
        return $this->model::query()->where('user_id', $userId)->active()->get();
    }

    public function findById(int $id, int $userId): Address
    {
        return $this->model::query()->where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data): Address
    {
        return $this->model->create($data);
    }

    public function update(Address $address, array $data): Address
    {
        $address->update($data);

        return $address->fresh();
    }

    public function delete(Address $address): bool
    {
        /** @var Model $address */
        $model = $address;

        return (bool) $model->delete();
    }

    public function forceDelete(Address $address): bool
    {
        return $address->forceDelete();
    }

    public function restore(Address $address): bool
    {
        return $address->restore();
    }

    public function search(string $search): Collection
    {
        return Address::query()
            ->where('name', 'like', "%{$search}%")
            ->with(['user'])
            ->get();
    }
}
