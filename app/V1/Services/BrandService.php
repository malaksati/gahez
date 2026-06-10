<?php

namespace App\V1\Services;

use App\Models\Brand;
use App\V1\Repositories\BrandRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandService
{
    public function __construct(
        protected BrandRepository $brands,
    ) {}

    public function getAllBrands(): Collection
    {
        return $this->brands->getAllBrands();
    }

    public function getPaginatedBrands(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->brands->getPaginatedBrands($perPage, $filters);
    }
    public function getBrandById(int $id): Brand
    {
        return $this->brands->getBrandById($id);
    }
    public function create(array $data): Brand
    {
        return $this->brands->create($data);
    }
    public function update(Brand $brand, array $data): bool
    {
        return $this->brands->update($brand, $data);
    }
    public function delete(Brand $brand): bool
    {
        return $this->brands->delete($brand);
    }
    public function forceDelete(Brand $brand): bool
    {
        return $this->brands->forceDelete($brand);
    }
    public function restore(Brand $brand): bool
    {
        return $this->brands->restore($brand);
    }
    public function search(string $search): Collection
    {
        return $this->brands->search($search);
    }
}
