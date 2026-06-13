<?php

namespace App\V1\Services;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Offer;
use App\Models\OfferRewardProduct;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\V1\Repositories\OfferRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferService
{
    /** @var array<int, array<int, array<string, mixed>>> */
    protected array $cartLinePricingsCache = [];

    public function __construct(
        protected OfferRepository $offers,
    ) {}

    public function getAllOffers(): Collection
    {
        return $this->offers->getAllOffers();
    }

    public function getPaginatedOffers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->offers->getPaginatedOffers($perPage, $filters);
    }

    public function getOfferById(int $id): Offer
    {
        return $this->offers->getOfferById($id);
    }

    public function getOffersForOfferable(string $offerableType, int $offerableId): Collection
    {
        return $this->offers->getOffersForOfferable($offerableType, $offerableId);
    }

    public function getActiveOffers(): Collection
    {
        return $this->offers->getActiveOffers();
    }

    public function getValidOffers(): Collection
    {
        return $this->offers->getValidOffers()
            ->filter(fn (Offer $offer) => $this->isOfferCurrentlyApplicable($offer))
            ->values();
    }

    public function create(array $data): Offer
    {
        return DB::transaction(function () use ($data) {
            $rewardProductIds = $data['reward_product_ids'] ?? [];
            unset($data['reward_product_ids'], $data['offerable_type_key']);

            $offer = $this->offers->create($this->normalizeOfferPayload($data));
            $this->syncRewardProducts($offer, $rewardProductIds);

            return $offer->fresh(['offerable', 'rewardProducts.product']);
        });
    }

    public function update(int $id, array $data): Offer
    {
        return DB::transaction(function () use ($id, $data) {
            $offer = $this->offers->getOfferById($id);
            $rewardProductIds = $data['reward_product_ids'] ?? null;
            unset($data['reward_product_ids'], $data['offerable_type_key']);

            $offer = $this->offers->update($offer, $this->normalizeOfferPayload($data, $offer));

            if (is_array($rewardProductIds)) {
                $this->syncRewardProducts($offer, $rewardProductIds);
            }

            return $offer->fresh(['offerable', 'rewardProducts.product']);
        });
    }

    public function delete(int $id): bool
    {
        $offer = $this->offers->getOfferById($id);

        return $this->offers->delete($offer);
    }

    public function isOfferCurrentlyApplicable(Offer $offer): bool
    {
        if (! $offer->is_active || ! $offer->isValid()) {
            return false;
        }

        if (! $offer->ends_when_out_of_stock) {
            return true;
        }

        return $this->offerHasAvailableStock($offer);
    }

    public function resolveBogoOfferForProduct(Product $product): ?Offer
    {
        return $this->resolveProductBogoOfferForProduct($product)
            ?? $this->resolveCategoryBogoOfferForProduct($product);
    }

    public function resolveProductBogoOfferForProduct(Product $product): ?Offer
    {
        $offers = $this->getValidOffers()->where('type', 'bogo');

        return $offers->first(
            fn (Offer $offer) => $offer->offerable_type === Product::class
                && (int) $offer->offerable_id === (int) $product->id
        );
    }

    public function resolveCategoryBogoOfferForProduct(Product $product): ?Offer
    {
        $offers = $this->getValidOffers()
            ->where('type', 'bogo')
            ->where('offerable_type', Category::class);

        foreach ($offers as $offer) {
            if ($this->productBelongsToCategoryTree($product, (int) $offer->offerable_id)) {
                return $offer;
            }
        }

        return null;
    }

    public function resolveDiscountOfferForProduct(Product $product): ?Offer
    {
        return $this->resolveProductScopedOffer($product, ['fixed', 'percentage']);
    }

    public function bogoFreeQuantityPerPaidUnit(Product $product): int
    {
        $offer = $this->resolveProductBogoOfferForProduct($product);

        if (! $offer || ! $offer->bogoAutoAddsBonusQuantity()) {
            return 0;
        }

        return max(1, (int) $offer->bogo_bonus_quantity);
    }

    /**
     * Maximum units that receive the offer discount; additional units are charged at full price.
     */
    public function maxDiscountedQuantityForProduct(Product $product): ?int
    {
        $offer = $this->resolveDiscountOfferForProduct($product);

        if (! $offer || ! $offer->max_discounted_quantity) {
            return null;
        }

        return (int) $offer->max_discounted_quantity;
    }

    /** @deprecated Use maxDiscountedQuantityForProduct(); offer caps discount only, not cart quantity. */
    public function maxCartQuantityForProduct(Product $product): ?int
    {
        return $this->maxDiscountedQuantityForProduct($product);
    }

    public function totalQuantityWithBogoBonus(Product $product, int $paidQuantity): int
    {
        $paidQuantity = max(1, $paidQuantity);
        $offer = $this->resolveProductBogoOfferForProduct($product);

        if (! $offer || ! $offer->bogoAutoAddsBonusQuantity()) {
            return $paidQuantity;
        }

        $buyQuantity = max(1, (int) $offer->bogo_buy_quantity);
        $bonusQuantity = max(1, (int) $offer->bogo_bonus_quantity);
        $completeSets = intdiv($paidQuantity, $buyQuantity);

        return $paidQuantity + ($completeSets * $bonusQuantity);
    }

    public function calculateBillableQuantity(Product $product, int $totalQuantity): int
    {
        if ($totalQuantity <= 0) {
            return 0;
        }

        $offer = $this->resolveProductBogoOfferForProduct($product);

        if (! $offer) {
            return $totalQuantity;
        }

        $pricing = $this->calculateBogoSubtotal($offer, 1.0, $totalQuantity);

        return $pricing['billable_quantity'];
    }

    /**
     * @return array{
     *     billable_quantity: int,
     *     unit_price: float,
     *     line_subtotal: float,
     *     original_subtotal: float,
     *     discounted_quantity: int,
     *     full_price_quantity: int,
     * }
     */
    public function pricingForCartItem(CartItem $item): array
    {
        $linePricings = $this->getCartLinePricingsForUser((int) $item->user_id);

        if (isset($linePricings[$item->id])) {
            return $linePricings[$item->id];
        }

        $product = $item->product;
        $variant = $item->variant;

        return $this->calculateLinePricing(
            $product,
            $variant,
            (int) $item->quantity,
            $item->productUnit ?? null,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCartLinePricingsForUser(int $userId): array
    {
        if (isset($this->cartLinePricingsCache[$userId])) {
            return $this->cartLinePricingsCache[$userId];
        }

        $cartItems = CartItem::query()
            ->with(['product.categories', 'product', 'variant', 'productUnit'])
            ->where('user_id', $userId)
            ->get();

        return $this->cartLinePricingsCache[$userId] = $this->calculateCartLinePricings($cartItems);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CartItem>  $cartItems
     * @return array<int, array{
     *     billable_quantity: int,
     *     unit_price: float,
     *     line_subtotal: float,
     *     original_subtotal: float,
     *     discounted_quantity: int,
     *     full_price_quantity: int,
     * }>
     */
    public function calculateCartLinePricings(\Illuminate\Support\Collection $cartItems): array
    {
        $basePricings = [];

        foreach ($cartItems as $item) {
            $basePricings[$item->id] = $this->calculateLinePricing(
                $item->product,
                $item->variant,
                (int) $item->quantity,
                $item->productUnit ?? null,
            );
        }

        $categoryOffers = $this->getValidOffers()
            ->where('type', 'bogo')
            ->where('offerable_type', Category::class);

        foreach ($categoryOffers as $offer) {
            $treeIds = $this->getCategoryTreeIds($this->resolveRootCategoryId((int) $offer->offerable_id));
            $eligibleItems = $cartItems->filter(function ($item) use ($treeIds) {
                $product = $item->product;
                if (! $product) {
                    return false;
                }

                $categoryIds = $product->relationLoaded('categories')
                    ? $product->categories->pluck('id')
                    : $product->categories()->pluck('categories.id');

                return $categoryIds->intersect($treeIds)->isNotEmpty();
            });

            if ($eligibleItems->isEmpty()) {
                continue;
            }

            $this->applyCategoryBogoToLines($offer, $eligibleItems, $basePricings);
        }

        return $basePricings;
    }

    /**
     * @return array{
     *     billable_quantity: int,
     *     unit_price: float,
     *     line_subtotal: float,
     *     original_subtotal: float,
     *     discounted_quantity: int,
     *     full_price_quantity: int,
     *     full_price_units: int,
     *     discounted_units: int,
     * }
     */
    public function calculateLinePricing(
        Product $product,
        ?ProductVariant $variant,
        int $totalQuantity,
        ?ProductUnit $productUnit = null,
    ): array {
        $totalQuantity = max(0, $totalQuantity);
        $basePrice = (float) ($productUnit?->final_price ?? $variant?->price ?? $product->price);
        $productBogo = $this->resolveProductBogoOfferForProduct($product);

        if ($productBogo && $totalQuantity > 0) {
            $bogoPricing = $this->calculateBogoSubtotal($productBogo, $basePrice, $totalQuantity);

            return [
                'billable_quantity' => $bogoPricing['billable_quantity'],
                'unit_price' => round($bogoPricing['line_subtotal'] / max(1, $totalQuantity), 2),
                'line_subtotal' => $bogoPricing['line_subtotal'],
                'original_subtotal' => round($basePrice * $totalQuantity, 2),
                'discounted_quantity' => $bogoPricing['discounted_units'],
                'full_price_quantity' => $bogoPricing['full_price_units'],
            ];
        }

        $discountOffer = $this->resolveDiscountOfferForProduct($product);
        $billableQuantity = $totalQuantity;
        $discountedQuantity = $billableQuantity;

        if ($discountOffer && $discountOffer->max_discounted_quantity) {
            $discountedQuantity = min($billableQuantity, (int) $discountOffer->max_discounted_quantity);
        }

        $fullPriceQuantity = max(0, $billableQuantity - $discountedQuantity);
        $discountedUnitPrice = $this->resolveDiscountedUnitPrice($basePrice, $product, $discountOffer);
        $lineSubtotal = ($discountedQuantity * $discountedUnitPrice) + ($fullPriceQuantity * $basePrice);

        return [
            'billable_quantity' => $billableQuantity,
            'unit_price' => $billableQuantity > 0 ? round($lineSubtotal / $billableQuantity, 2) : $basePrice,
            'line_subtotal' => round($lineSubtotal, 2),
            'original_subtotal' => round($basePrice * $billableQuantity, 2),
            'discounted_quantity' => $discountedQuantity,
            'full_price_quantity' => $fullPriceQuantity,
        ];
    }

    /**
     * @return array{
     *     line_subtotal: float,
     *     billable_quantity: int,
     *     full_price_units: int,
     *     discounted_units: int,
     * }
     */
    public function calculateBogoSubtotal(Offer $offer, float $unitPrice, int $totalQuantity): array
    {
        $buyQuantity = max(1, min(2, (int) $offer->bogo_buy_quantity));
        $bonusQuantity = max(1, min(2, (int) $offer->bogo_bonus_quantity));
        $setSize = $buyQuantity + $bonusQuantity;
        $discountedUnitPrice = $this->calculateBogoDiscountedUnitPrice($unitPrice, $offer);

        $fullSets = intdiv($totalQuantity, $setSize);
        $remainder = $totalQuantity % $setSize;

        $fullUnits = ($fullSets * $buyQuantity) + min($remainder, $buyQuantity);
        $discountedUnits = ($fullSets * $bonusQuantity) + max(0, $remainder - $buyQuantity);
        $lineSubtotal = ($fullUnits * $unitPrice) + ($discountedUnits * $discountedUnitPrice);

        return [
            'line_subtotal' => round($lineSubtotal, 2),
            'billable_quantity' => $fullUnits + $discountedUnits,
            'full_price_units' => $fullUnits,
            'discounted_units' => $discountedUnits,
        ];
    }

    public function resolveFreeDeliveryThreshold(): ?float
    {
        $offer = $this->getValidOffers()
            ->where('type', 'free_delivery')
            ->filter(fn (Offer $item) => $item->min_cart_amount !== null)
            ->sortBy(fn (Offer $item) => (float) $item->min_cart_amount)
            ->first();

        if ($offer) {
            return (float) $offer->min_cart_amount;
        }

        return null;
    }

    public function qualifiesForFreeDelivery(float $subtotal): bool
    {
        $threshold = $this->resolveFreeDeliveryThreshold();

        if ($threshold === null) {
            return false;
        }

        return $subtotal >= $threshold;
    }

    public function resolveThresholdGiftOffer(float $cartSubtotal): ?Offer
    {
        return $this->getValidOffers()
            ->where('type', 'threshold_gift')
            ->filter(fn (Offer $offer) => $cartSubtotal >= (float) $offer->min_cart_amount)
            ->filter(fn (Offer $offer) => $this->thresholdGiftHasSelectableRewards($offer))
            ->sortByDesc(fn (Offer $offer) => (float) $offer->min_cart_amount)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildCheckoutPreview(int $userId, float $cartSubtotal): array
    {
        $giftOffer = $this->resolveThresholdGiftOffer($cartSubtotal);
        $rewardProducts = [];

        if ($giftOffer) {
            $giftOffer->loadMissing('rewardProducts.product');
            $rewardProducts = $giftOffer->rewardProducts
                ->filter(fn (OfferRewardProduct $row) => $row->product && $row->product->isPurchasable() && $row->product->hasStock())
                ->map(fn (OfferRewardProduct $row) => [
                    'id' => $row->product_id,
                    'name' => $row->product->getTranslation('name', app()->getLocale(), false)
                        ?: $row->product->getTranslation('name', 'en', false),
                    'thumbnail' => $row->product->thumbnail,
                ])
                ->values()
                ->all();
        }

        return [
            'cart_subtotal' => round($cartSubtotal, 2),
            'free_delivery_threshold' => $this->resolveFreeDeliveryThreshold(),
            'qualifies_for_free_delivery' => $this->qualifiesForFreeDelivery($cartSubtotal),
            'gift_offer' => $giftOffer ? [
                'id' => $giftOffer->id,
                'name' => $giftOffer->getTranslation('name', app()->getLocale(), false)
                    ?: $giftOffer->getTranslation('name', 'en', false),
                'min_cart_amount' => (float) $giftOffer->min_cart_amount,
                'reward_products' => $rewardProducts,
                'requires_selection' => count($rewardProducts) > 0,
            ] : null,
        ];
    }

    public function validateGiftSelection(?int $giftOfferId, ?int $giftProductId, float $cartSubtotal): void
    {
        $giftOffer = $this->resolveThresholdGiftOffer($cartSubtotal);

        if (! $giftOffer) {
            if ($giftOfferId || $giftProductId) {
                throw ValidationException::withMessages([
                    'gift_product_id' => [__('messages.Cart does not qualify for a gift offer.')],
                ]);
            }

            return;
        }

        if (! $giftProductId) {
            throw ValidationException::withMessages([
                'gift_product_id' => [__('messages.Please select your free gift product.')],
            ]);
        }

        if ((int) $giftOfferId !== (int) $giftOffer->id) {
            throw ValidationException::withMessages([
                'gift_offer_id' => [__('messages.Selected gift offer is no longer available.')],
            ]);
        }

        $giftOffer->loadMissing('rewardProducts');
        $allowedIds = $giftOffer->rewardProducts->pluck('product_id')->map(fn ($id) => (int) $id);

        if (! $allowedIds->contains((int) $giftProductId)) {
            throw ValidationException::withMessages([
                'gift_product_id' => [__('messages.Selected gift product is not part of this offer.')],
            ]);
        }

        $product = Product::query()->find($giftProductId);

        if (! $product || ! $product->isPurchasable() || ! $product->hasStock()) {
            throw ValidationException::withMessages([
                'gift_product_id' => [__('messages.Selected gift product is out of stock.')],
            ]);
        }
    }

    protected function thresholdGiftHasSelectableRewards(Offer $offer): bool
    {
        $offer->loadMissing('rewardProducts.product');

        return $offer->rewardProducts->contains(
            fn (OfferRewardProduct $row) => $row->product
                && $row->product->isPurchasable()
                && $row->product->hasStock()
        );
    }

    protected function calculateBogoDiscountedUnitPrice(float $unitPrice, Offer $offer): float
    {
        $type = (string) ($offer->bogo_bonus_discount_type ?? 'percentage');
        $value = (float) ($offer->bogo_bonus_discount_value ?? 100);

        if ($type === 'fixed') {
            return round(max(0, $unitPrice - $value), 2);
        }

        return round(max(0, $unitPrice * (1 - ($value / 100))), 2);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, CartItem>  $eligibleItems
     * @param  array<int, array<string, mixed>>  $linePricings
     */
    protected function applyCategoryBogoToLines(Offer $offer, \Illuminate\Support\Collection $eligibleItems, array &$linePricings): void
    {
        $buyQuantity = max(1, min(2, (int) $offer->bogo_buy_quantity));
        $bonusQuantity = max(1, min(2, (int) $offer->bogo_bonus_quantity));
        $setSize = $buyQuantity + $bonusQuantity;

        $units = [];

        foreach ($eligibleItems as $item) {
            $basePrice = (float) ($item->variant?->price ?? $item->product->price);

            for ($i = 0; $i < (int) $item->quantity; $i++) {
                $units[] = [
                    'cart_item_id' => $item->id,
                    'unit_price' => $basePrice,
                ];
            }
        }

        if ($units === []) {
            return;
        }

        usort($units, fn (array $left, array $right) => $left['unit_price'] <=> $right['unit_price']);

        $lineTotals = [];
        $lineFullUnits = [];
        $lineDiscountedUnits = [];

        foreach ($eligibleItems as $item) {
            $lineTotals[$item->id] = 0.0;
            $lineFullUnits[$item->id] = 0;
            $lineDiscountedUnits[$item->id] = 0;
        }

        $totalUnits = count($units);

        for ($offset = 0; $offset + $setSize <= $totalUnits; $offset += $setSize) {
            $setUnits = array_slice($units, $offset, $setSize);

            foreach (array_slice($setUnits, 0, $buyQuantity) as $unit) {
                $lineTotals[$unit['cart_item_id']] += $unit['unit_price'];
                $lineFullUnits[$unit['cart_item_id']]++;
            }

            foreach (array_slice($setUnits, $buyQuantity, $bonusQuantity) as $unit) {
                $lineTotals[$unit['cart_item_id']] += $this->calculateBogoDiscountedUnitPrice($unit['unit_price'], $offer);
                $lineDiscountedUnits[$unit['cart_item_id']]++;
            }
        }

        $remainder = $totalUnits % $setSize;
        if ($remainder > 0) {
            $remainderUnits = array_slice($units, $totalUnits - $remainder, $remainder);

            foreach (array_slice($remainderUnits, 0, min($remainder, $buyQuantity)) as $unit) {
                $lineTotals[$unit['cart_item_id']] += $unit['unit_price'];
                $lineFullUnits[$unit['cart_item_id']]++;
            }

            foreach (array_slice($remainderUnits, $buyQuantity) as $unit) {
                $lineTotals[$unit['cart_item_id']] += $this->calculateBogoDiscountedUnitPrice($unit['unit_price'], $offer);
                $lineDiscountedUnits[$unit['cart_item_id']]++;
            }
        }

        foreach ($eligibleItems as $item) {
            $quantity = (int) $item->quantity;
            $basePrice = (float) ($item->variant?->price ?? $item->product->price);
            $lineSubtotal = round($lineTotals[$item->id], 2);

            $linePricings[$item->id] = [
                'billable_quantity' => $quantity,
                'unit_price' => $quantity > 0 ? round($lineSubtotal / $quantity, 2) : $basePrice,
                'line_subtotal' => $lineSubtotal,
                'original_subtotal' => round($basePrice * $quantity, 2),
                'discounted_quantity' => $lineDiscountedUnits[$item->id],
                'full_price_quantity' => $lineFullUnits[$item->id],
            ];
        }
    }

    public function resolveRootCategoryId(int $categoryId): int
    {
        $category = Category::query()->find($categoryId);

        if (! $category) {
            return $categoryId;
        }

        while ($category->parent_id) {
            $category = Category::query()->find($category->parent_id) ?? $category;
            if (! $category->parent_id) {
                break;
            }
        }

        return (int) $category->id;
    }

    /**
     * @return list<int>
     */
    public function getCategoryTreeIds(int $rootCategoryId): array
    {
        $ids = [$rootCategoryId];
        $queue = [$rootCategoryId];

        while ($queue !== []) {
            $parentId = array_shift($queue);
            $children = Category::query()->where('parent_id', $parentId)->pluck('id');

            foreach ($children as $childId) {
                $ids[] = (int) $childId;
                $queue[] = (int) $childId;
            }
        }

        return array_values(array_unique($ids));
    }

    public function productBelongsToCategoryTree(Product $product, int $categoryId): bool
    {
        $treeIds = $this->getCategoryTreeIds($this->resolveRootCategoryId($categoryId));
        $productCategoryIds = $product->relationLoaded('categories')
            ? $product->categories->pluck('id')
            : $product->categories()->pluck('categories.id');

        return $productCategoryIds->intersect($treeIds)->isNotEmpty();
    }

    protected function resolveProductScopedOffer(Product $product, string|array $types): ?Offer
    {
        $types = (array) $types;

        $offers = $this->getValidOffers()->whereIn('type', $types);

        $direct = $offers->first(
            fn (Offer $offer) => $offer->offerable_type === Product::class
                && (int) $offer->offerable_id === (int) $product->id
        );

        if ($direct) {
            return $direct;
        }

        $categoryIds = $product->relationLoaded('categories')
            ? $product->categories->pluck('id')
            : $product->categories()->pluck('categories.id');

        return $offers->first(
            fn (Offer $offer) => $offer->offerable_type === Category::class
                && $categoryIds->contains((int) $offer->offerable_id)
        );
    }

    protected function resolveDiscountedUnitPrice(float $basePrice, Product $product, ?Offer $discountOffer): float
    {
        if ($discountOffer) {
            if ($discountOffer->type === 'percentage') {
                return round(max(0, $basePrice - (($basePrice * (float) $discountOffer->value) / 100)), 2);
            }

            if ($discountOffer->type === 'fixed') {
                return round(max(0, $basePrice - (float) $discountOffer->value), 2);
            }
        }

        $discount = (float) ($product->discount ?? 0);
        if ($discount <= 0) {
            return $basePrice;
        }

        if ($product->discount_type === 'percentage') {
            return round(max(0, $basePrice - (($basePrice * $discount) / 100)), 2);
        }

        return round(max(0, $basePrice - $discount), 2);
    }

    protected function offerHasAvailableStock(Offer $offer): bool
    {
        return match ($offer->type) {
            'threshold_gift' => $offer->rewardProducts()
                ->whereHas('product', fn ($query) => $query->where('is_active', true)->where(function ($product) {
                    $product->where('type', 'variable')
                        ->whereHas('variants', fn ($variants) => $variants->where(function ($stock) {
                            $stock->where('stock', '>', 0)
                                ->orWhere(fn ($untracked) => $untracked->whereNull('stock')->where('is_in_stock', true));
                        }))
                        ->orWhere(function ($simple) {
                            $simple->where('type', 'simple')
                                ->whereHas('productUnits', fn ($units) => $units->where('is_active', true)->where(function ($stock) {
                                    $stock->where('stock', '>', 0)
                                        ->orWhere(fn ($untracked) => $untracked->whereNull('stock')->where('is_in_stock', true));
                                }));
                        });
                }))
                ->exists(),
            'bogo', 'fixed', 'percentage' => $this->offerableHasStock($offer),
            default => true,
        };
    }

    protected function offerableHasStock(Offer $offer): bool
    {
        if ($offer->offerable_type === Product::class && $offer->offerable_id) {
            $product = Product::query()->find($offer->offerable_id);

            return $product && $product->hasStock() && $product->is_active;
        }

        if ($offer->offerable_type === Category::class && $offer->offerable_id) {
            return Product::query()
                ->whereHas('categories', fn ($query) => $query->where('categories.id', $offer->offerable_id))
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'variable')
                        ->whereHas('variants', fn ($variants) => $variants->where(function ($stock) {
                            $stock->where('stock', '>', 0)
                                ->orWhere(fn ($untracked) => $untracked->whereNull('stock')->where('is_in_stock', true));
                        }))
                        ->orWhere(function ($simple) {
                            $simple->where('type', 'simple')
                                ->whereHas('productUnits', fn ($units) => $units->where('is_active', true)->where(function ($stock) {
                                    $stock->where('stock', '>', 0)
                                        ->orWhere(fn ($untracked) => $untracked->whereNull('stock')->where('is_in_stock', true));
                                }));
                        });
                })
                ->exists();
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeOfferPayload(array $data, ?Offer $existing = null): array
    {
        $type = (string) ($data['type'] ?? $existing?->type ?? 'fixed');

        if (in_array($type, ['threshold_gift', 'free_delivery'], true)) {
            $data['offerable_id'] = null;
            $data['offerable_type'] = null;
        }

        if ($type === 'free_delivery') {
            $data['value'] = 0;
        }

        if ($type === 'threshold_gift') {
            $data['value'] = 1;
        }

        if (! in_array($type, ['fixed', 'percentage'], true)) {
            $data['max_discounted_quantity'] = null;
        }

        if (! in_array($type, ['threshold_gift', 'free_delivery'], true)) {
            $data['min_cart_amount'] = null;
        }

        if ($type === 'bogo') {
            $data['max_discounted_quantity'] = null;
            $data['value'] = 0;
            $data['bogo_buy_quantity'] = max(1, min(2, (int) ($data['bogo_buy_quantity'] ?? $existing?->bogo_buy_quantity ?? 1)));
            $data['bogo_bonus_quantity'] = max(1, min(2, (int) ($data['bogo_bonus_quantity'] ?? $existing?->bogo_bonus_quantity ?? 1)));
            $data['bogo_bonus_discount_type'] = in_array(
                (string) ($data['bogo_bonus_discount_type'] ?? $existing?->bogo_bonus_discount_type ?? 'percentage'),
                ['percentage', 'fixed'],
                true
            ) ? (string) ($data['bogo_bonus_discount_type'] ?? $existing?->bogo_bonus_discount_type ?? 'percentage') : 'percentage';
            $data['bogo_bonus_discount_value'] = max(0, (float) ($data['bogo_bonus_discount_value'] ?? $existing?->bogo_bonus_discount_value ?? 100));
        }

        return $data;
    }

    /**
     * @param  list<int|string>  $productIds
     */
    protected function syncRewardProducts(Offer $offer, array $productIds): void
    {
        $offer->rewardProducts()->delete();

        foreach (array_values($productIds) as $index => $productId) {
            if (! $productId) {
                continue;
            }

            OfferRewardProduct::query()->create([
                'offer_id' => $offer->id,
                'product_id' => (int) $productId,
                'sort_order' => $index + 1,
            ]);
        }
    }
}
