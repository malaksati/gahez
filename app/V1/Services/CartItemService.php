<?php

namespace App\V1\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
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
        protected CheckoutSettingsService $checkoutSettings,
    ) {}

    public function getUserCartItems(int $userId): Collection
    {
        return $this->cartItems->getUserCartItems($userId);
    }

    public function addOrIncrement(
        int $userId,
        Product $product,
        ?int $variantId = null,
        int $quantity = 1,
        ?int $productUnitId = null,
    ): CartItem {
        $paidQuantity = max(1, $quantity);
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);
        $productUnitId = $this->resolveProductUnitIdForProduct($product, $productUnitId, $variantId);
        $this->assertProductUnitMatchesVariant($product, $variantId, $productUnitId);
        $totalToAdd = $this->offers->totalQuantityWithBogoBonus($product, $paidQuantity);

        $existingQuantity = $this->existingCartQuantity($userId, $product->id, $variantId, $productUnitId);
        $targetTotal = $existingQuantity + $totalToAdd;

        $this->assertCartStockAvailable($userId, $product, $variantId, $targetTotal, true, $productUnitId);

        return $this->cartItems->addOrIncrement($userId, $product, $variantId, $totalToAdd, $productUnitId);
    }

    public function updateQuantity(
        int $userId,
        Product $product,
        int $quantity,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): CartItem {
        $quantity = max(1, $quantity);
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);
        $productUnitId = $this->resolveProductUnitIdForProduct($product, $productUnitId, $variantId);
        $this->assertProductUnitMatchesVariant($product, $variantId, $productUnitId);

        $this->assertCartStockAvailable($userId, $product, $variantId, $quantity, true, $productUnitId);

        return $this->cartItems->updateQuantity($userId, $product, $quantity, $variantId, $productUnitId);
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
        $this->assertCartStockAvailable($userId, $product, $variantId, $quantity, true);

        return $this->cartItems->updateQuantityById($userId, $cartItemId, $quantity)
            ->load(['product', 'variant']);
    }

    public function removeItem(
        int $userId,
        Product $product,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): bool {
        $variantId = $this->resolveVariantIdForProduct($product, $variantId);
        $productUnitId = $this->resolveProductUnitIdForProduct($product, $productUnitId);

        return $this->cartItems->removeItem($userId, $product, $variantId, $productUnitId);
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

    public function getCartLineCount(int $userId): int
    {
        return $this->cartItems->getCartLineCount($userId);
    }

    public function getCheckoutPreview(int $userId): array
    {
        $subtotal = $this->calculateCartSubtotal($userId);
        $lineCount = $this->getCartLineCount($userId);
        $preview = $this->offers->buildCheckoutPreview($userId, $subtotal);
        $appliedCoupon = $this->getAppliedCoupon($userId);
        $qualifiesForFreeDelivery = (bool) ($preview['qualifies_for_free_delivery'] ?? false)
            || ($appliedCoupon && $appliedCoupon->grantsFreeDelivery());

        $defaultAddress = Address::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        return array_merge($preview, [
            'cart_limits' => $this->checkoutSettings->cartLimits($subtotal, $lineCount),
            'shipping' => $this->checkoutSettings->shippingPayload(
                $qualifiesForFreeDelivery,
                $defaultAddress?->latitude,
                $defaultAddress?->longitude,
            ),
        ]);
    }

    protected function existingCartQuantity(
        int $userId,
        int $productId,
        ?int $variantId,
        ?int $productUnitId = null,
    ): int {
        return (int) (CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->when($variantId, fn ($query) => $query->where('variant_id', $variantId))
            ->when(! $variantId, fn ($query) => $query->whereNull('variant_id'))
            ->when($productUnitId, fn ($query) => $query->where('product_unit_id', $productUnitId))
            ->when(! $productUnitId, fn ($query) => $query->whereNull('product_unit_id'))
            ->value('quantity') ?? 0);
    }

    protected function resolveProductUnitIdForProduct(Product $product, ?int $productUnitId, ?int $variantId = null): ?int
    {
        $units = $product->productUnits()->where('is_active', true)->get();

        if ($units->isEmpty()) {
            return null;
        }

        if ($variantId) {
            $unitsForVariant = $units->filter(
                fn (ProductUnit $unit) => ! $unit->product_variant_id
                    || (int) $unit->product_variant_id === (int) $variantId,
            );

            if ($unitsForVariant->isNotEmpty()) {
                $units = $unitsForVariant;
            }
        }

        if ($productUnitId) {
            $match = $units->firstWhere('id', $productUnitId);

            if (! $match) {
                throw ValidationException::withMessages([
                    'product_unit_id' => [__('messages.Selected product unit is unavailable.')],
                ]);
            }

            return $match->id;
        }

        $default = $units->firstWhere('is_default', true) ?? $units->first();

        return $default?->id;
    }

    protected function assertProductUnitMatchesVariant(Product $product, ?int $variantId, ?int $productUnitId): void
    {
        if (! $productUnitId || ! $variantId) {
            return;
        }

        $unit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->whereKey($productUnitId)
            ->first();

        if (! $unit || ! $unit->product_variant_id) {
            return;
        }

        if ((int) $unit->product_variant_id !== (int) $variantId) {
            throw ValidationException::withMessages([
                'product_unit_id' => [__('messages.Product unit variant mismatch')],
            ]);
        }
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
        ?int $productUnitId = null,
    ): void {
        $quantity = max(1, $quantity);

        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages([
                'product' => ['This product is not available for purchase.'],
            ]);
        }

        $stockTarget = null;

        if ($product->isVariable()) {
            $variant = $product->variants()->whereKey($variantId)->first();
            $stockTarget = $variant;
        } elseif ($productUnitId) {
            $stockTarget = ProductUnit::query()
                ->where('product_id', $product->id)
                ->whereKey($productUnitId)
                ->first();

            if (! $stockTarget || ! $stockTarget->is_active) {
                throw ValidationException::withMessages([
                    'product_unit_id' => [__('messages.Selected product unit is unavailable.')],
                ]);
            }
        } else {
            $stockTarget = ProductUnit::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
        }

        if (! $stockTarget) {
            throw ValidationException::withMessages([
                'quantity' => ['Selected product option is unavailable.'],
            ]);
        }

        if (! $stockTarget->tracksStock()) {
            if (! $stockTarget->isInStock()) {
                throw ValidationException::withMessages([
                    'quantity' => ['This product is currently out of stock.'],
                ]);
            }

            return;
        }

        $existingQuantity = $this->existingCartQuantity($userId, $product->id, $variantId, $productUnitId);
        $required = $absolute ? $quantity : ($existingQuantity + $quantity);

        if ((int) $stockTarget->stock < $required) {
            $name = $stockTarget instanceof ProductVariant
                ? ($stockTarget->getTranslation('name', app()->getLocale(), false)
                    ?: $stockTarget->getTranslation('name', 'en', false)
                    ?: $stockTarget->sku)
                : ($stockTarget instanceof ProductUnit
                    ? ($stockTarget->formattedLabel() ?: $product->getTranslation('name', app()->getLocale(), false))
                    : ($product->getTranslation('name', app()->getLocale(), false)
                        ?: $product->getTranslation('name', 'en', false)
                        ?: $product->sku));

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
