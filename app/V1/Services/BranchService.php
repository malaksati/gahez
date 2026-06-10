<?php

namespace App\V1\Services;

use App\Models\Branch;
use App\V1\Repositories\BranchRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchService
{
    public function __construct(
        protected BranchRepository $branches,
    ) {}

    public function getAllBranches(): Collection
    {
        return $this->branches->getAllBranches();
    }

    public function getPaginatedBranches(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->branches->getPaginatedBranches($perPage, $filters);
    }
    public function getBranchById(int $id): Branch
    {
        return $this->branches->getBranchById($id);
    }
    public function create(array $data): Branch
    {
        return $this->branches->create($data);
    }
    public function update(Branch $branch, array $data): bool
    {
        return $this->branches->update($branch, $data);
    }
    public function delete(Branch $branch): bool
    {
        return $this->branches->delete($branch);
    }
    public function forceDelete(Branch $branch): bool
    {
        return $this->branches->forceDelete($branch);
    }
    public function restore(Branch $branch): bool
    {
        return $this->branches->restore($branch);
    }
    public function search(string $search): Collection
    {
        return $this->branches->search($search);
    }
}
