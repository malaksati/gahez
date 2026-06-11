<?php

namespace App\V1\Services;

use App\Models\Category;
use App\V1\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        protected CategoryRepository $categories,
    ) {}

    public function getAllCategories(): Collection
    {
        return $this->categories->getAllCategories();
    }

    public function getPaginatedCategories(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->categories->getPaginatedCategories($perPage, $filters);
    }

    public function getPaginatedCategorySections(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->categories->getPaginatedRootCategories($perPage, $filters);
    }

    public function getCategoriesGroupedByParent(): Collection
    {
        return $this->categories->getAllCategoriesOrdered()->groupBy(
            fn (Category $category) => (string) $category->parent_id,
        );
    }

    public function flattenCategorySection(Category $root, Collection $byParent): Collection
    {
        $flat = new Collection;

        $walk = function (Category $category, int $depth) use (&$walk, $byParent, &$flat): void {
            $category->setAttribute('tree_depth', $depth);
            $flat->push($category);

            $children = $this->sortCategoriesAsc(
                $byParent->get((string) $category->id, new Collection),
            );

            foreach ($children as $child) {
                $walk($child, $depth + 1);
            }
        };

        $walk($root, 0);

        return $flat;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filterCategoryTreeByFilters(Collection $flat, array $filters): Collection
    {
        $flat = $this->filterCategoryTreeBySearch($flat, $filters);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $isActive = $filters['status'] === 'active';
            $flat = $flat->filter(fn (Category $category) => (bool) $category->is_active === $isActive);
        }

        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $isFeatured = $filters['featured'] === '1';
            $flat = $flat->filter(fn (Category $category) => (bool) $category->is_featured === $isFeatured);
        }

        return $flat->values();
    }

    public function restrictCategoryTreeToParentsOnly(Collection $flat): Collection
    {
        return $flat
            ->filter(fn (Category $category) => (int) ($category->tree_depth ?? 0) === 0)
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function filterCategoryTreeBySearch(Collection $flat, array $filters): Collection
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search === '') {
            return $flat;
        }

        $matching = $flat->filter(fn (Category $category) => $this->categoryMatchesSearch($category, $search));

        if ($matching->isEmpty()) {
            return new Collection;
        }

        $keepIds = collect();
        $byId = $flat->keyBy('id');

        foreach ($matching as $category) {
            $current = $category;

            while ($current) {
                $keepIds->push($current->id);
                $parentId = $current->parent_id;
                $current = $parentId ? $byId->get($parentId) : null;
            }
        }

        return $flat
            ->filter(fn (Category $category) => $keepIds->contains($category->id))
            ->values();
    }

    public function filterCategoriesMatchingSearch(Collection $categories, string $search): Collection
    {
        $search = trim($search);

        if ($search === '') {
            return $categories;
        }

        return $categories
            ->filter(fn (Category $category) => $this->categoryMatchesSearch($category, $search))
            ->values();
    }

    public function categoryMatchesSearch(Category $category, string $search): bool
    {
        $needle = mb_strtolower(trim($search));

        if ($needle === '') {
            return true;
        }

        $nameEn = mb_strtolower((string) $category->getTranslation('name', 'en', false));
        $nameAr = mb_strtolower((string) $category->getTranslation('name', 'ar', false));

        return str_contains($nameEn, $needle) || str_contains($nameAr, $needle);
    }

    public function getOrphanCategories(): Collection
    {
        return $this->categories->getOrphanCategories();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categories->getCategoryById($id);
    }

    public function getActiveCategories(): Collection
    {
        return $this->categories->getActiveCategories();
    }

    public function getActiveCategoriesTreeFlat(): Collection
    {
        return $this->categories->getActiveCategoriesTreeFlat();
    }

    public function getFeaturedCategories(): Collection
    {
        return $this->categories->getFeaturedCategories();
    }

    public function getRootCategories(): Collection
    {
        return $this->categories->getRootCategories();
    }

    public function getCategoriesWithParent(): Collection
    {
        return $this->categories->getCategoriesWithParent();
    }

    public function getCategoriesWithChildren(): Collection
    {
        return $this->categories->getCategoriesWithChildren();
    }

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $data = $this->normalizeSortOrderInput($data);
            $parentId = $data['parent_id'] ?? null;
            $sortOrder = isset($data['sort_order']) ? max(1, (int) $data['sort_order']) : null;
            $data['sort_order'] = $this->resolveSortOrderForCreate($sortOrder, $parentId);

            return $this->categories->create($data);
        });
    }

    public function update(Category $category, array $data): bool
    {
        return DB::transaction(function () use ($category, $data) {
            $data = $this->normalizeSortOrderInput($data);

            $parentChanged = array_key_exists('parent_id', $data)
                && (int) ($data['parent_id'] ?? 0) !== (int) ($category->parent_id ?? 0);

            if ($parentChanged) {
                $this->compactSortOrderAfterRemoval($category);
                $newParentId = $data['parent_id'] ?? null;
                $sortOrder = isset($data['sort_order']) ? max(1, (int) $data['sort_order']) : null;
                $data['sort_order'] = $this->resolveSortOrderForCreate($sortOrder, $newParentId);
            } elseif (array_key_exists('sort_order', $data)) {
                $data['sort_order'] = $this->reassignSortOrder($category, (int) $data['sort_order']);
            }

            return $this->categories->update($category, $data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeSortOrderInput(array $data): array
    {
        if (array_key_exists('sort_order', $data) && ($data['sort_order'] === '' || $data['sort_order'] === null)) {
            unset($data['sort_order']);
        }

        if (array_key_exists('parent_id', $data) && $data['parent_id'] === '') {
            $data['parent_id'] = null;
        }

        return $data;
    }

    protected function resolveSortOrderForCreate(?int $sortOrder, ?int $parentId): int
    {
        if ($sortOrder === null || $sortOrder < 1) {
            return ((int) $this->siblingQuery($parentId)->max('sort_order')) + 1;
        }

        $this->siblingQuery($parentId)
            ->where('sort_order', '>=', $sortOrder)
            ->increment('sort_order');

        return $sortOrder;
    }

    protected function reassignSortOrder(Category $category, int $newOrder): int
    {
        $newOrder = max(1, $newOrder);
        $oldOrder = (int) ($category->sort_order ?? 0);
        $parentId = $category->parent_id;

        if ($oldOrder < 1) {
            $this->siblingQuery($parentId)
                ->where('id', '!=', $category->id)
                ->where('sort_order', '>=', $newOrder)
                ->increment('sort_order');

            return $newOrder;
        }

        if ($oldOrder === $newOrder) {
            return $newOrder;
        }

        if ($newOrder < $oldOrder) {
            $this->siblingQuery($parentId)
                ->where('id', '!=', $category->id)
                ->where('sort_order', '>=', $newOrder)
                ->where('sort_order', '<', $oldOrder)
                ->increment('sort_order');
        } else {
            $this->siblingQuery($parentId)
                ->where('id', '!=', $category->id)
                ->where('sort_order', '>', $oldOrder)
                ->where('sort_order', '<=', $newOrder)
                ->decrement('sort_order');
        }

        return $newOrder;
    }

    protected function compactSortOrderAfterRemoval(Category $category): void
    {
        $oldOrder = (int) ($category->sort_order ?? 0);

        if ($oldOrder < 1) {
            return;
        }

        $this->siblingQuery($category->parent_id)
            ->where('id', '!=', $category->id)
            ->where('sort_order', '>', $oldOrder)
            ->decrement('sort_order');
    }

    public function sortCategoriesAsc(Collection $categories): Collection
    {
        return $categories
            ->sortBy(fn (Category $category) => [(int) $category->sort_order, (int) $category->id])
            ->values();
    }

    protected function siblingQuery(?int $parentId): Builder
    {
        $query = Category::query();

        if ($parentId === null) {
            return $query->whereNull('parent_id');
        }

        return $query->where('parent_id', $parentId);
    }

    public function delete(Category $category): bool
    {
        return $this->categories->delete($category);
    }

    public function forceDelete(Category $category): bool
    {
        return $this->categories->forceDelete($category);
    }

    public function restore(Category $category): bool
    {
        return $this->categories->restore($category);
    }

    public function search(string $search): Collection
    {
        return $this->categories->search($search);
    }

    public function getCategoryTree(): Collection
    {
        return $this->categories->getCategoryTree();
    }

    public function hasChildren(Category $category): bool
    {
        return $this->categories->hasChildren($category);
    }

    public function getCategoryBySlug(string $slug): ?Category
    {
        return $this->categories->getCategoryBySlug($slug);
    }
}
