<?php

namespace App\V1\Repositories;

use App\Models\Brand;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BrandRepository
{
    use AppliesInsensitiveSearch;

    protected $model;

    public function __construct(Brand $brand)
    {
        $this->model = $brand;
    }

    public function getAllBrands(): Collection
    {
        return $this->model::query()->get();
    }

    public function getPaginatedBrands(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query()->withCount('products');

        if (! empty($filters['search'])) {
            $this->applyTranslatableNameSearch($query, (string) $filters['search']);
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getBrandById(int $id): Brand
    {
        return $this->model::query()->findOrFail($id);
    }

    public function create(array $data): Brand
    {
        return $this->model::query()->create($data);
    }

    public function update(Brand $brand, array $data): bool
    {
        return (bool) $brand->update($data);
    }

    public function delete(Brand $brand): bool
    {
        /** @var Model $brand */
        $model = $brand;

        return (bool) $model->delete();
    }

    public function forceDelete(Brand $brand): bool
    {
        return (bool) $brand->forceDelete();
    }

    public function restore(Brand $brand): bool
    {
        return (bool) $brand->restore();
    }

    public function search(string $search): Collection
    {
        return $this->model::query()->where('name', 'like', "%{$search}%")->get();
    }
}
