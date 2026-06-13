<?php

namespace App\V1\Repositories;

use App\Models\Category;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CategoryRepository
{
    use AppliesInsensitiveSearch;

    public function getAllCategories(): Collection
    {
        return Category::with(['parent', 'children'])->get();
    }

    public function getAllCategoriesOrdered(): Collection
    {
        return Category::with(['parent', 'children'])
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getOrphanCategories(): Collection
    {
        return Category::with(['parent', 'children'])
            ->withCount('products')
            ->whereNotNull('parent_id')
            ->whereDoesntHave('parent')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getPaginatedRootCategories(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = Category::query()
            ->withCount('products')
            ->whereNull('parent_id');

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $rootIds = $this->rootIdsForSearch($search);

            $query->where(function ($q) use ($search, $rootIds) {
                $this->applyTranslatableNameSearch($q, $search);

                if ($rootIds !== []) {
                    $q->orWhereIn('id', $rootIds);
                }
            });
        }

        if (isset($filters['status']) && $filters['status'] != '') {
            $query->where('is_active', $filters['status'] == 'active');
        }

        if (isset($filters['featured']) && $filters['featured'] != '') {
            $query->where('is_featured', $filters['featured'] == 1);
        }

        if (isset($filters['parent_id']) && $filters['parent_id'] != '' && $filters['parent_id'] != 'root') {
            $query->where('id', $this->resolveRootAncestorId((int) $filters['parent_id']));
        }

        $this->applyCategorySort($query, (string) ($filters['sort'] ?? 'sort_order'));

        return $query->paginate($perPage)->withQueryString();
    }

    public function resolveRootAncestorId(int $categoryId): int
    {
        $visited = [];
        $currentId = $categoryId;

        while ($currentId && ! in_array($currentId, $visited, true)) {
            $visited[] = $currentId;

            $category = Category::query()
                ->select('id', 'parent_id')
                ->find($currentId);

            if (! $category || ! $category->parent_id) {
                return $currentId;
            }

            $currentId = (int) $category->parent_id;
        }

        return $categoryId;
    }

    /**
     * @return list<int>
     */
    protected function rootIdsForSearch(string $search): array
    {
        $matchingQuery = Category::query()->whereNotNull('parent_id');
        $this->applyTranslatableNameSearch($matchingQuery, $search);

        return $matchingQuery
            ->get(['id', 'parent_id'])
            ->map(fn (Category $category) => $this->resolveRootAncestorId((int) $category->id))
            ->unique()
            ->values()
            ->all();
    }

    public function getPaginatedCategories(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Category::with(['parent', 'children']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $this->applyTranslatableNameSearch($query, (string) $filters['search']);
        }

        // Apply status filter
        if (isset($filters['status']) && $filters['status'] != '') {
            $query->where('is_active', $filters['status'] == 'active');
        }

        // Apply featured filter
        if (isset($filters['featured']) && $filters['featured'] != '') {
            $query->where('is_featured', $filters['featured'] == 1);
        }

        // Apply parent filter
        if (isset($filters['parent_id']) && $filters['parent_id'] != '') {
            if ($filters['parent_id'] == 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        $this->applyCategorySort($query, (string) ($filters['sort'] ?? 'sort_order'));

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<Category>  $query
     */
    protected function applyCategorySort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            'latest' => $query->latest(),
            default => $query->orderBy('sort_order')->orderBy('id'),
        };
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::with(['parent', 'children', 'products'])->find($id);
    }

    /**
     * Get active categories
     */
    public function getActiveCategories(): Collection
    {
        return Category::active()
            ->with(['parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Active categories ordered parent-first, then children (depth-first), with tree_depth set on each model.
     */
    public function getActiveCategoriesTreeFlat(): Collection
    {
        $all = Category::query()
            ->active()
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $byParent = $all->groupBy(
            fn (Category $category) => (string) ($category->parent_id ?? 'root'),
        );

        $flat = new Collection;

        $walk = function (string $parentKey, int $depth) use (&$walk, $byParent, &$flat): void {
            $siblings = $byParent->get($parentKey, new Collection)
                ->sortBy(fn (Category $category) => [(int) $category->sort_order, (int) $category->id])
                ->values();

            foreach ($siblings as $category) {
                $category->setAttribute('tree_depth', $depth);
                $flat->push($category);
                $walk((string) $category->id, $depth + 1);
            }
        };

        $walk('root', 0);

        foreach ($all->whereNotIn('id', $flat->pluck('id')) as $orphan) {
            $orphan->setAttribute('tree_depth', 0);
            $flat->push($orphan);
            $walk((string) $orphan->id, 1);
        }

        return $flat;
    }

    /**
     * Get featured categories
     */
    public function getFeaturedCategories(): Collection
    {
        return Category::featured()
            ->active()
            ->with(['parent', 'children'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get root categories (categories with no parent)
     */
    public function getRootCategories(): Collection
    {
        return Category::root()
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Get categories with their parent
     */
    public function getCategoriesWithParent(): Collection
    {
        return Category::with('parent')->get();
    }

    /**
     * Get categories with their children
     */
    public function getCategoriesWithChildren(): Collection
    {
        return Category::with('children')->get();
    }

    /**
     * Create a new category
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Update a category
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Delete a category (soft delete)
     */
    public function delete(Category $category): bool
    {
        /** @var Model $model */
        $model = $category;

        return (bool) $model->delete();
    }

    /**
     * Force delete a category
     */
    public function forceDelete(Category $category): bool
    {
        return $category->forceDelete();
    }

    /**
     * Restore a soft deleted category
     */
    public function restore(Category $category): bool
    {
        return $category->restore();
    }

    /**
     * Search categories by name
     */
    public function search(string $search): Collection
    {
        return Category::query()
            ->where('name', 'like', "%{$search}%")
            ->with(['parent', 'children'])
            ->get();
    }

    /**
     * Get category tree (nested structure)
     */
    public function getCategoryTree(): Collection
    {
        return Category::root()
            ->with('children')
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Check if category has children
     */
    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    /**
     * Get category by slug (if you add slug field later)
     */
    public function getCategoryBySlug(string $slug): ?Category
    {
        return Category::query()->where('slug', $slug)->first();
    }
}
