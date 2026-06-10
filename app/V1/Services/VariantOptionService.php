<?php

namespace App\V1\Services;

use App\Models\VariantOption;
use App\V1\Repositories\VariantOptionRepository;
use Illuminate\Database\Eloquent\Collection;

class VariantOptionService
{
    public function __construct(
        protected VariantOptionRepository $variantOptions,
    ) {}

    public function getAllVariantOptions(): Collection
    {
        return $this->variantOptions->getAllVariantOptions();
    }

    public function getVariantOptionById(int $id): VariantOption
    {
        return $this->variantOptions->getVariantOptionById($id);
    }

    public function create(array $data): VariantOption
    {
        return $this->variantOptions->create($data);
    }

    public function update(VariantOption $variantOption, array $data): bool
    {
        return $this->variantOptions->update($variantOption, $data);
    }

    public function delete(VariantOption $variantOption): bool
    {
        return $this->variantOptions->delete($variantOption);
    }

    public function forceDelete(VariantOption $variantOption): bool
    {
        return $this->variantOptions->forceDelete($variantOption);
    }

    public function restore(VariantOption $variantOption): bool
    {
        return $this->variantOptions->restore($variantOption);
    }

    public function search(string $search): Collection
    {
        return $this->variantOptions->search($search);
    }
}
