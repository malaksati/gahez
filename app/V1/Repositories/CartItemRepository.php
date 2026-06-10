<?php

namespace App\V1\Repositories;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CartItemRepository
{
    protected $model;

    public function __construct(CartItem $cartItem)
    {
        $this->model = $cartItem;
    }

    public function getUserCartItems(int $userId): Collection
    {
        return $this->model::query()
            ->with(['product.categories', 'product.productUnits.unit', 'product', 'variant', 'productUnit.unit'])
            ->where('user_id', $userId)
            ->get();
    }

    public function getCartLineCount(int $userId): int
    {
        return $this->model::query()->where('user_id', $userId)->count();
    }

    public function addOrIncrement(
        int $userId,
        Product $product,
        ?int $variantId = null,
        int $quantity = 1,
        ?int $productUnitId = null,
    ): CartItem {
        $quantity = max(1, $quantity);
        $cartItem = $this->findCartLine($userId, $product->id, $variantId, $productUnitId);

        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);

            return $cartItem->fresh();
        }

        return $this->model::query()->create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'product_unit_id' => $productUnitId,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(
        int $userId,
        Product $product,
        int $quantity,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): CartItem {
        $cartItem = $this->findCartLine($userId, $product->id, $variantId, $productUnitId);

        if (! $cartItem) {
            throw ValidationException::withMessages([
                'product' => ['This product is not in your cart. Add it before updating quantity.'],
            ]);
        }

        return $this->setCartItemQuantity($cartItem, $quantity);
    }

    public function updateQuantityById(int $userId, int $cartItemId, int $quantity): CartItem
    {
        $cartItem = $this->model::query()
            ->where('user_id', $userId)
            ->whereKey($cartItemId)
            ->firstOrFail();

        return $this->setCartItemQuantity($cartItem, $quantity);
    }

    protected function setCartItemQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->update(['quantity' => max(1, $quantity)]);

        return $cartItem->fresh();
    }

    public function removeOrDecrement(int $userId, Product $product, ?int $variantId = null, ?int $productUnitId = null): CartItem
    {
        $cartItem = $this->findCartLine($userId, $product->id, $variantId, $productUnitId);

        if (! $cartItem) {
            return new CartItem([
                'user_id' => $userId,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'product_unit_id' => $productUnitId,
                'quantity' => 0,
            ]);
        }

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');

            return $cartItem->fresh();
        }

        $cartItem->delete();

        return new CartItem([
            'user_id' => $userId,
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'product_unit_id' => $productUnitId,
            'quantity' => 0,
        ]);
    }

    public function removeItem(int $userId, Product $product, ?int $variantId = null, ?int $productUnitId = null): bool
    {
        $cartItem = $this->findCartLine($userId, $product->id, $variantId, $productUnitId);

        if (! $cartItem) {
            return false;
        }

        return (bool) $cartItem->delete();
    }

    protected function findCartLine(
        int $userId,
        int $productId,
        ?int $variantId,
        ?int $productUnitId = null,
    ): ?CartItem {
        $query = $this->model::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId);

        if ($variantId) {
            $query->where('variant_id', $variantId);
        } else {
            $query->whereNull('variant_id');
        }

        if ($productUnitId) {
            $query->where('product_unit_id', $productUnitId);
        } else {
            $query->whereNull('product_unit_id');
        }

        return $query->first();
    }

    public function clearCart(int $userId): bool
    {
        return $this->model::query()->where('user_id', $userId)->delete();
    }

    public function getCartTotal(int $userId): float
    {
        $cartItems = $this->model::query()->where('user_id', $userId)->with(['product'])->get();
        $totalQuantity = 0;
        foreach ($cartItems as $cartItem) {
            $totalQuantity += $cartItem->quantity;
        }

        return $totalQuantity;
    }

    public function getCartTotalPrice(int $userId): float
    {
        $cartItems = $this->model::query()->where('user_id', $userId)->with(['product'])->get();
        $totalPrice = 0;
        foreach ($cartItems as $cartItem) {
            $totalPrice += $cartItem->quantity * $cartItem->product->price;
        }

        return $totalPrice;
    }

    public function getCartTotalDiscount(int $userId): float
    {
        $cartItems = $this->model::query()->where('user_id', $userId)->with(['product'])->get();
        $totalDiscount = 0;
        foreach ($cartItems as $cartItem) {
            $totalDiscount += $cartItem->quantity * $cartItem->product->discount;
        }

        return $totalDiscount;
    }
}
