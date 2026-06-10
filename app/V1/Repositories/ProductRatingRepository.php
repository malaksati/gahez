<?php

namespace App\V1\Repositories;

use App\Models\ProductRating;
use App\V1\Repositories\Concerns\AppliesInsensitiveSearch;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ProductRatingRepository
{
    use AppliesInsensitiveSearch;

    public function __construct(
        protected ProductRating $model,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model::query()->with(['product', 'user']);

        if (! empty($filters['search'])) {
            $search = (string) $filters['search'];
            $term = $this->insensitiveLikeTerm($search);

            $query->where(function ($q) use ($search, $term) {
                $q->whereRaw('LOWER(comment) LIKE ?', [$term])
                    ->orWhereHas('product', fn ($productQuery) => $this->applyTranslatableNameSearch($productQuery, $search))
                    ->orWhereHas('user', fn ($userQuery) => $this->applyColumnsSearchInsensitive($userQuery, ['name', 'email'], $search));
            });
        }

        if (isset($filters['rating']) && $filters['rating'] !== '') {
            $query->where('rating', (int) $filters['rating']);
        }

        if (isset($filters['visibility']) && $filters['visibility'] !== '') {
            $query->where('is_visible', $filters['visibility'] === 'visible');
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function update(ProductRating $rating, array $data): ProductRating
    {
        $rating->update($data);

        return $rating->fresh(['product', 'user']);
    }

    public function delete(ProductRating $rating): bool
    {
        /** @var Model $rating */
        $model = $rating;

        return (bool) $model->delete();
    }
}
