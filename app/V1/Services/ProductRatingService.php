<?php

namespace App\V1\Services;

use App\Models\ProductRating;
use App\V1\Repositories\ProductRatingRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRatingService
{
    public function __construct(
        protected ProductRatingRepository $ratings,
    ) {}

    public function getPaginated(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->ratings->getPaginated($perPage, $filters);
    }

    public function rateProduct(int $userId, int $productId, int $rating, ?string $comment = null): ProductRating
    {
        return ProductRating::query()->updateOrCreate(
            [
                'product_id' => $productId,
                'user_id' => $userId,
            ],
            [
                'rating' => $rating,
                'comment' => $comment,
                'is_visible' => true,
            ],
        );
    }

    public function update(ProductRating $rating, array $data): ProductRating
    {
        return $this->ratings->update($rating, $data);
    }

    public function delete(ProductRating $rating): bool
    {
        return $this->ratings->delete($rating);
    }
}
