<?php

namespace App\V1\Repositories;

use App\Models\Variant;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class VariantRepository
{
    use AppliesInsensitiveSearch;

    protected $model;

    public function __construct(Variant $variant)
    {
        $this->model = $variant;
    }

    public function getAllVariants(): Collection
    {
        return $this->model::with('options')->get();
    }

    public function getPaginatedVariants(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->with('options');

        // Apply search filter
        if (! empty($filters['search'])) {
            $this->applyTranslatableNameSearch($query, (string) $filters['search']);
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        // Apply required filter
        if (isset($filters['required']) && $filters['required'] !== '') {
            $query->where('is_required', $filters['required'] === '1');
        }

        return $query->latest()->paginate($perPage);
    }

    public function getVariantById(int $id): Variant
    {
        return $this->model->with('options')->findOrFail($id);
    }

    public function getActiveVariants(): Collection
    {
        return $this->model->with('options')->active()->get();
    }

    public function getRequiredVariants(): Collection
    {
        return $this->model->with('options')->required()->get();
    }

    public function create(array $data): Variant
    {
        return $this->model->create($data);
    }

    public function update(Variant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    public function delete(Variant $variant): bool
    {
        /** @var Model $variant */
        $model = $variant;

        return (bool) $model->delete();
    }

    public function forceDelete(Variant $variant): bool
    {
        return (bool) $variant->forceDelete();
    }

    public function restore(Variant $variant): bool
    {
        return (bool) $variant->restore();
    }

    public function search(string $search): Collection
    {
        $term = '%'.trim($search).'%';

        return $this->model->query()
            ->where(function ($q) use ($term) {
                $q->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", [$term], 'and')
                    ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", [$term], 'or');
            })
            ->where('is_active', true)
            ->where('is_required', true)
            ->with('options')
            ->get();
    }

    public function getVariantOptions(int $variantId): Collection
    {
        return $this->model->findOrFail($variantId)->options;
    }

    public function attachOption(Variant $variant, int $optionId): void
    {
        $variant->options()->attach($optionId);
    }

    public function detachOption(Variant $variant, int $optionId): void
    {
        $variant->options()->detach($optionId);
    }

    public function syncOptions(Variant $variant, array $optionIds): void
    {
        $variant->options()->sync($optionIds);
    }
}
