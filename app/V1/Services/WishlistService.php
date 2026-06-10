<?php

namespace App\V1\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    public function listForUser(int $userId): Collection
    {
        return Wishlist::query()
            ->where('user_id', $userId)
            ->with(['product.brand', 'product.images'])
            ->latest()
            ->get();
    }

    /**
     * @return array{wishlisted: bool, message: string}
     */
    public function toggle(User $user, Product $product): array
    {
        $existing = Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return [
                'wishlisted' => false,
                'message' => 'Product removed from wishlist.',
            ];
        }

        Wishlist::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return [
            'wishlisted' => true,
            'message' => 'Product added to wishlist.',
        ];
    }
}
