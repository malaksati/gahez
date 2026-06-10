<?php

namespace App\V1\Repositories;

use App\Models\VariantOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class VariantOptionRepository
{
    protected $model;

    public function __construct(VariantOption $variantOption)
    {
        $this->model = $variantOption;
    }

    public function getAllVariantOptions(): Collection
    {
        return $this->model::query()->with('variant')->get();
    }

    public function getVariantOptionById(int $id): VariantOption
    {
        return $this->model::query()->with('variant')->findOrFail($id);
    }

    public function create(array $data): VariantOption
    {
        return $this->model::query()->create($data);
    }

    public function update(VariantOption $variantOption, array $data): bool
    {
        return (bool) $variantOption->update($data);
    }

    public function delete(VariantOption $variantOption): bool
    {
        /** @var Model $variantOption */
        $model = $variantOption;

        return (bool) $model->delete();
    }

    public function forceDelete(VariantOption $variantOption): bool
    {
        return (bool) $variantOption->forceDelete();
    }

    public function restore(VariantOption $variantOption): bool
    {
        return (bool) $variantOption->restore();
    }

    public function search(string $search): Collection
    {
        return $this->model::query()->where('name', 'like', "%{$search}%")->with('variant')->get();
    }
}
