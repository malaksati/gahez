<?php

namespace App\V1\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use App\V1\Repositories\CartItemRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class CartItemService
{
    public function __construct(
        protected CartItemRepository $cartItems,
        protected OfferService $offers,
    ) {}

    public function getUserCartItems(int $userId): Collection
    {
        return $this->cartItems->getUserCartItems($userId);
    }

    public function addOrIncrement(int $userId, Product $product, ?int $variantId = null, int $quantity = 1): CartItem
    {
        $paidQuantity = max(1, $quantity);
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);
        $totalToAdd = $this->offers->totalQuantityWithBogoBonus($product, $paidQuantity);

        $existingQuantity = $this->existingCartQuantity($userId, $product->id, $variantId);
        $targetTotal = $existingQuantity + $totalToAdd;

        $this->assertCartStockAvailable($userId, $product, $variantId, $targetTotal, absolute: true);

        return $this->cartItems->addOrIncrement($userId, $product, $variantId, $totalToAdd);
    }

    public function updateQuantity(int $userId, Product $product, int $quantity, ?int $variantId = null): CartItem
    {
        $quantity = max(1, $quantity);
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);

        $this->assertCartStockAvailable($userId, $product, $variantId, $quantity, absolute: true);

        return $this->cartItems->updateQuantity($userId, $product, $quantity, $variantId);
    }

    public function updateQuantityById(int $userId, int $cartItemId, int $quantity): CartItem
    {
        $quantity = max(1, $quantity);
        $cartItem = CartItem::query()
            ->with(['product', 'variant'])
            ->where('user_id', $userId)
            ->whereKey($cartItemId)
            ->firstOrFail();

        $product = $cartItem->product;
        $variantId = $cartItem->variant_id;

        $this->resolveVariantIdForProduct($product, $variantId);
        $this->assertCartStockAvailable($userId, $product, $variantId, $quantity, absolute: true);

        return $this->cartItems->updateQuantityById($userId, $cartItemId, $quantity)
            ->load(['product', 'variant']);
    }

    public function removeItem(int $userId, Product $product, ?int $variantId = null): bool
    {
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);

        return $this->cartItems->removeItem($userId, $product, $variantId);
    }

    public function clearCart(int $userId): bool
    {
        $this->forgetAppliedCoupon($userId);

        return $this->cartItems->clearCart($userId);
    }

    public function getCartTotal(int $userId): float
    {
        return $this->cartItems->getCartTotal($userId);
    }

    public function getCartTotalPrice(int $userId): float
    {
        return $this->calculateCartSubtotal($userId);
    }

    public function getCartTotalDiscount(int $userId): float
    {
        return $this->cartItems->getCartTotalDiscount($userId);
    }

    public function calculateCartSubtotal(int $userId): float
    {
        $cartItems = $this->getUserCartItems($userId);
        $linePricings = $this->offers->getCartLinePricingsForUser($userId);
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $subtotal += $linePricings[$item->id]['line_subtotal'] ?? 0;
        }

        return round($subtotal, 2);
    }

    public function getCheckoutPreview(int $userId): array
    {
        $subtotal = $this->calculateCartSubtotal($userId);

        return $this->offers->buildCheckoutPreview($userId, $subtotal);
    }

    protected function existingCartQuantity(int $userId, int $productId, ?int $variantId): int
    {
        return (int) (CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->when($variantId, fn ($query) => $query->where('variant_id', $variantId))
            ->when(! $variantId, fn ($query) => $query->whereNull('variant_id'))
            ->value('quantity') ?? 0);
    }

    protected function resolveVariantIdForProduct(Product $product, ?int $variantId): ?int
    {
        if ($product->isVariable()) {
            if (! $variantId) {
                throw ValidationException::withMessages([
                    'variant_id' => ['Variant is required for this product.'],
                ]);
            }

            $variant = $product->variants()->whereKey($variantId)->first();

            if (! $variant || ! $variant->is_active) {
                throw ValidationException::withMessages([
                    'variant_id' => ['Selected variant is unavailable.'],
                ]);
            }

            return $variant->id;
        }

        if ($variantId !== null) {
            throw ValidationException::withMessages([
                'variant_id' => ['This product does not have variants.'],
            ]);
        }

        return null;
    }

    protected function assertCartStockAvailable(
        int $userId,
        Product $product,
        ?int $variantId,
        int $quantity,
        bool $absolute = false,
    ): void {
        $quantity = max(1, $quantity);

        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages([
                'product' => ['This product is not available for purchase.'],
            ]);
        }

        $stockTarget = $product;

        if ($product->isVariable()) {
            $variant = $product->variants()->whereKey($variantId)->first();
            $stockTarget = $variant;
        }

        if (! $stockTarget->tracksStock()) {
            if (! $stockTarget->isInStock()) {
                throw ValidationException::withMessages([
                    'quantity' => ['This product is currently out of stock.'],
                ]);
            }

            return;
        }

        $existingQuantity = $this->existingCartQuantity($userId, $product->id, $variantId);
        $required = $absolute ? $quantity : ($existingQuantity + $quantity);

        if ((int) $stockTarget->stock < $required) {
            $name = $stockTarget instanceof \App\Models\ProductVariant
                ? ($stockTarget->getTranslation('name', app()->getLocale(), false)
                    ?: $stockTarget->getTranslation('name', 'en', false)
                    ?: $stockTarget->sku)
                : ($product->getTranslation('name', app()->getLocale(), false)
                    ?: $product->getTranslation('name', 'en', false)
                    ?: $product->sku);

            throw ValidationException::withMessages([
                'quantity' => ["Insufficient stock for {$name}."],
            ]);
        }
    }

    /**
     * @return array{coupon: Coupon, discount: float, subtotal: float, grants_free_delivery: bool}
     */
    public function applyCoupon(int $userId, string $code): array
    {
        $user = User::query()->findOrFail($userId);
        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'code' => ['Coupon not found.'],
            ]);
        }

        if (! $coupon->isValid()) {
            throw ValidationException::withMessages([
                'code' => ['Coupon is invalid or expired.'],
            ]);
        }

        $cartSubtotal = $this->calculateCartSubtotal($userId);

        if ($cartSubtotal < (float) $coupon->min_cart_amount) {
            throw ValidationException::withMessages([
                'code' => ['Cart total does not meet the minimum amount for this coupon.'],
            ]);
        }

        $cachedCouponId = Cache::get($this->couponCacheKey($userId));
        $isAlreadyApplied = $cachedCouponId && (int) $cachedCouponId === (int) $coupon->id;

        if (! $isAlreadyApplied) {
            $usabilityError = $coupon->usabilityErrorForUser($user);

            if ($usabilityError) {
                throw ValidationException::withMessages([
                    'code' => [$usabilityError],
                ]);
            }
        }

        $discount = $coupon->calculateDiscount($cartSubtotal);

        Cache::put($this->couponCacheKey($userId), $coupon->id, now()->addDay());

        return [
            'coupon' => $coupon,
            'discount' => $discount,
            'subtotal' => $cartSubtotal,
            'grants_free_delivery' => $coupon->grantsFreeDelivery(),
        ];
    }

    public function getAppliedCoupon(int $userId): ?Coupon
    {
        $couponId = Cache::get($this->couponCacheKey($userId));

        return $couponId ? Coupon::query()->find($couponId) : null;
    }

    public function forgetAppliedCoupon(int $userId): void
    {
        Cache::forget($this->couponCacheKey($userId));
    }

    protected function couponCacheKey(int $userId): string
    {
        return 'cart_coupon_user_'.$userId;
    }
}
