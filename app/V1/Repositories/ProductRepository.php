<?php

namespace App\V1\Repositories;

use App\Models\Product;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductRepository
{
    use AppliesInsensitiveSearch;

    protected $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function getAllProducts(): Collection
    {
        return $this->model::with(['categories', 'images', 'variants', 'variantValues'])->get();
    }

    public function getPaginatedProducts(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $with = ['brand', 'categories', 'images', 'variants', 'variantValues', 'productUnits.unit'];
        $query = $this->model::with($with);
        $this->applyAdminListFilters($query, $filters);
        $this->applyAdminListSort($query, (string) ($filters['sort'] ?? 'latest'));

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @param  Builder<Product>  $query
     * @param  array<string, mixed>  $filters
     */
    public function applyAdminListFilters($query, array $filters): void
    {
        if (! empty($filters['search'])) {
            $term = $this->insensitiveLikeTerm((string) $filters['search']);

            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.en'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.ar'))) LIKE ?", [$term])
                    ->orWhereRaw('LOWER(sku) LIKE ?', [$term]);
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('is_active', $filters['status'] === 'active');
        }

        if (isset($filters['featured']) && $filters['featured'] !== '') {
            $query->where('is_featured', $filters['featured'] === '1');
        }

        if (isset($filters['approved']) && $filters['approved'] !== '') {
            $query->where('is_approved', $filters['approved'] === '1');
        }

        if (isset($filters['type']) && $filters['type'] !== '') {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category_id']) && $filters['category_id'] !== '') {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $minPrice = (float) $filters['min_price'];
            $query->where(function ($q) use ($minPrice) {
                $q->where(function ($simple) use ($minPrice) {
                    $simple->where('type', 'simple')
                        ->whereHas('productUnits', fn ($units) => $units
                            ->where('is_active', true)
                            ->where('price', '>=', $minPrice));
                })->orWhere(function ($variable) use ($minPrice) {
                    $variable->where('type', 'variable')
                        ->whereHas('variants', fn ($variants) => $variants
                            ->where('is_active', true)
                            ->where('price', '>=', $minPrice));
                });
            });
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $maxPrice = (float) $filters['max_price'];
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($simple) use ($maxPrice) {
                    $simple->where('type', 'simple')
                        ->whereHas('productUnits', fn ($units) => $units
                            ->where('is_active', true)
                            ->where('price', '<=', $maxPrice));
                })->orWhere(function ($variable) use ($maxPrice) {
                    $variable->where('type', 'variable')
                        ->whereHas('variants', fn ($variants) => $variants
                            ->where('is_active', true)
                            ->where('price', '<=', $maxPrice));
                });
            });
        }

        if (isset($filters['stock']) && $filters['stock'] !== '') {
            $query->where(function ($q) {
                $q->where(function ($simple) {
                    $simple->where('type', 'simple')
                        ->whereHas('productUnits', function ($units) {
                            $units->where('is_active', true)
                                ->where(function ($stock) {
                                    $stock->where('stock', '>', 0)
                                        ->orWhere(function ($untracked) {
                                            $untracked->whereNull('stock')->where('is_in_stock', true);
                                        });
                                });
                        });
                })->orWhere(function ($variable) {
                    $variable->where('type', 'variable')
                        ->whereHas('variants', function ($variants) {
                            $variants->where(function ($stock) {
                                $stock->where('stock', '>', 0)
                                    ->orWhere(function ($untracked) {
                                        $untracked->whereNull('stock')->where('is_in_stock', true);
                                    });
                            });
                        });
                });
            });
        }

        if (isset($filters['is_new']) && $filters['is_new'] !== '') {
            $query->where('is_new', (bool) $filters['is_new']);
        }

        if (isset($filters['is_bookable']) && $filters['is_bookable'] !== '') {
            $query->where('is_bookable', (bool) $filters['is_bookable']);
        }
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function applyAdminListSort($query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') ASC"),
            'name_desc' => $query->orderByRaw("JSON_EXTRACT(name, '$.en') DESC"),
            'price_asc' => $query->latest(),
            'price_desc' => $query->latest(),
            default => $query->latest(),
        };
    }

    public function getProductById(int $id): Product
    {
        return $this->model::with([
            'categories',
            'images',
            'productUnits.unit',
            'variants.values.variantOption.variant',
            'relatedProducts',
            'ratings.user',
        ])->findOrFail($id);
    }

    public function getProductBySlug(string $slug): Product
    {
        return $this->model::with([
            'categories',
            'images',
            'productUnits.unit',
            'variants.values.variantOption.variant',
            'relatedProducts',
            'ratings.user',
        ])->where('slug', $slug)->firstOrFail();
    }

    public function getActiveProducts(): Collection
    {
        return $this->model::with(['categories', 'images'])->approved()->active()->get();
    }

    public function getFeaturedProducts(): Collection
    {
        return Product::featured()
            ->active()
            ->approved()
            ->with(['categories', 'images'])
            ->get();
    }

    public function getNewProducts(int $limit = 10): Collection
    {
        return Product::new()
            ->active()
            ->approved()
            ->with(['categories', 'images'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getProductsByCategory(int $categoryId): Collection
    {
        return Product::whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })
            ->active()
            ->approved()
            ->with(['images'])
            ->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        /** @var Model $product */
        $model = $product;

        return (bool) $model->delete();
    }

    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    public function restore(Product $product): bool
    {
        return $product->restore();
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
            ->where('is_approved', true)
            ->with(['categories', 'images'])
            ->get();
    }

    public function syncCategories(Product $product, array $categoryIds): void
    {
        $product->categories()->sync($categoryIds);
    }

    public function attachCategory(Product $product, int $categoryId): void
    {
        if (! $product->categories()->where('categories.id', $categoryId)->exists()) {
            $product->categories()->attach($categoryId);
        }
    }

    public function detachCategory(Product $product, int $categoryId): void
    {
        $product->categories()->detach($categoryId);
    }
}
