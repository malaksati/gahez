<?php

namespace App\V1\Repositories;

use App\Models\Branch;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository
{
    use AppliesInsensitiveSearch;

    protected $model;

    public function __construct(Branch $branch)
    {
        $this->model = $branch;
    }

    public function getAllBranches(): Collection
    {
        return $this->model->all();
    }

    public function getPaginatedBranches(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query();

        if (! empty($filters['search'])) {
            $term = $this->insensitiveLikeTerm((string) $filters['search']);

            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.en'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.ar'))) LIKE ?", [$term])
                    ->orWhereRaw('LOWER(address) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$term]);
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        $sort = (string) ($filters['sort'] ?? 'latest');

        match ($sort) {
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en')", ['desc']),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en')", ['desc']),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    public function getBranchById(int $id): Branch
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Branch
    {
        return $this->model->create($data);
    }
    public function update(Branch $branch, array $data): bool
    {
        return $branch->update($data);
    }
    public function delete(Branch $branch): bool
    {
        /** @var Model $branch */
        $model = $branch;

        return (bool) $model->delete();
    }
    public function forceDelete(Branch $branch): bool
    {
        return (bool) $branch->forceDelete();
    }
    public function restore(Branch $branch): bool
    {
        return (bool) $branch->restore();
    }
    public function search(string $search): Collection
    {
        return $this->model::query()->where('name', 'like', "%{$search}%")->get();
    }
}
