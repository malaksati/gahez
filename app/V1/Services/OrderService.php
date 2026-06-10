<?php

namespace App\V1\Services;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\NewOrderForAdminNotification;
use App\V1\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const PAYMENT_METHODS = ['cash_on_delivery', 'wallet'];

    public function __construct(
        protected OrderRepository $orders,
        protected NotificationService $notifications,
        protected CartItemService $cartItems,
        protected OfferService $offers,
        protected PointService $points,
        protected CheckoutSettingsService $checkoutSettings,
        protected GoalService $goals,
    ) {}

    public function getPaginatedOrders(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orders->getPaginatedOrders($perPage, $filters);
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orders->getOrderById($id);
    }

    public function getOrdersByUser(int $userId): Collection
    {
        return $this->orders->getOrdersByUser($userId);
    }

    public function getPaginatedOrdersForUser(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orders->getPaginatedOrdersForUser($userId, $perPage, $filters);
    }

    public function getOrderByIdForUser(int $id, int $userId): ?Order
    {
        return $this->orders->getOrderByIdForUser($id, $userId);
    }

    public function create(array $data): Order
    {
        $order = $this->orders->create($data);
        $this->notifications->notifyAdmins(new NewOrderForAdminNotification($order));

        return $order;
    }

    /**
     * Create an order from the authenticated user's cart (checkout).
     *
     * @param  array<string, mixed>  $data
     */
    public function createOrderFromCart(int $userId, array $data): Order
    {
        return DB::transaction(function () use ($userId, $data) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            $address = Address::query()
                ->where('user_id', $userId)
                ->where('id', $data['address_id'])
                ->first();

            if (! $address) {
                throw ValidationException::withMessages([
                    'address_id' => ['Invalid address.'],
                ]);
            }

            $cartItems = $this->cartItems->getUserCartItems($userId);

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty.'],
                ]);
            }

            $validItems = $this->validateAndCleanCartItems($userId, $cartItems);

            if ($validItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['All items in your cart are no longer available.'],
                ]);
            }

            $originalTotal = 0.0;
            $subTotal = 0.0;
            $linePricings = $this->offers->calculateCartLinePricings($validItems);

            foreach ($validItems as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $quantity = (int) $item->quantity;
                $pricing = $linePricings[$item->id];

                $originalTotal += $pricing['original_subtotal'];
                $this->assertStockAvailable(
                    $product,
                    $variant,
                    $quantity,
                    $variant?->id ?? $item->variant_id,
                    $item->product_unit_id,
                );
                $subTotal += $pricing['line_subtotal'];
            }

            $subTotal = round($subTotal, 2);
            $orderDiscount = round(max(0, $originalTotal - $subTotal), 2);

            $this->checkoutSettings->assertCartLimits($validItems->count(), $subTotal);

            $this->offers->validateGiftSelection(
                isset($data['gift_offer_id']) ? (int) $data['gift_offer_id'] : null,
                isset($data['gift_product_id']) ? (int) $data['gift_product_id'] : null,
                $subTotal,
            );

            $giftOfferId = isset($data['gift_offer_id']) ? (int) $data['gift_offer_id'] : null;
            $giftProductId = isset($data['gift_product_id']) ? (int) $data['gift_product_id'] : null;

            $coupon = $this->resolveCheckoutCoupon($user, $data, $subTotal);
            $couponDiscount = $this->calculateCouponDiscount($coupon, $subTotal);

            $freeDelivery = $this->offers->qualifiesForFreeDelivery($subTotal)
                || ($coupon && $coupon->grantsFreeDelivery());
            $isFastShipping = filter_var($data['is_fast_shipping'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $shippingDay = $this->checkoutSettings->assertValidShippingDay($data['shipping_day'] ?? '');
            $shippingBreakdown = $this->checkoutSettings->computeShipping($isFastShipping, $freeDelivery);
            $totalShipping = $shippingBreakdown['total_shipping'];

            $totalBeforeWallet = round(max(0, $subTotal - $couponDiscount + $totalShipping), 2);

            $paymentMethod = strtolower(trim((string) ($data['payment_method'] ?? 'cash_on_delivery')));
            $useWallet = filter_var($data['use_wallet'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $this->assertValidPaymentMethod($paymentMethod);

            $checkoutPayment = $this->resolveCheckoutPayment(
                $paymentMethod,
                $useWallet,
                $user,
                $totalBeforeWallet,
            );

            $order = $this->orders->create([
                'user_id' => $userId,
                'branch_id' => 1,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $address->phone ?? $user->phone,
                'sub_total' => round($subTotal, 2),
                'order_discount' => $orderDiscount,
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $couponDiscount,
                'total_shipping' => $totalShipping,
                'wallet_used' => $checkoutPayment['wallet_used'],
                'total' => $checkoutPayment['total'],
                'status' => 'pending',
                'payment_status' => $checkoutPayment['payment_status'],
                'payment_method' => $paymentMethod,
                'paid_at' => $checkoutPayment['paid_at'],
                'notes' => $data['notes'] ?? null,
                'address_id' => $address->id,
                'shipping_address_snapshot' => $this->buildAddressSnapshot($address),
                'shipping_day' => $shippingDay,
                'is_fast_shipping' => $shippingBreakdown['is_fast_shipping'],
                'fast_shipping_fee' => $shippingBreakdown['fast_shipping_fee'],
                'total_commission' => 0,
                'refund_status' => 'none',
                'gift_offer_id' => $giftOfferId,
                'gift_product_id' => $giftProductId,
            ]);

            $this->logOrderEvent($order, 'order_placed', null, 'pending', $userId);

            if ($checkoutPayment['payment_status'] === 'paid') {
                $this->logOrderEvent($order, 'payment_change', 'pending', 'paid', $userId, [
                    'payment_method' => $paymentMethod,
                ]);
            }

            foreach ($validItems as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $quantity = (int) $item->quantity;
                $pricing = $linePricings[$item->id];
                $productNameEn = $product->getTranslation('name', 'en', false);
                $productNameAr = $product->getTranslation('name', 'ar', false);
                $variantNameEn = $variant?->getTranslation('name', 'en', false);
                $variantNameAr = $variant?->getTranslation('name', 'ar', false);
                $productUnit = $item->product_unit_id
                    ? ProductUnit::with('unit')->find($item->product_unit_id)
                    : null;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'product_unit_id' => $productUnit?->id,
                    'unit_name' => $productUnit?->displayUnitName('en'),
                    'unit_name_ar' => $productUnit?->displayUnitName('ar'),
                    'unit_factor' => $productUnit ? max(1, (int) $productUnit->factor) : null,
                    'product_name' => $productNameEn ?: ($productNameAr ?: $product->sku),
                    'product_name_ar' => $productNameAr ?: null,
                    'product_slug' => (string) $product->slug,
                    'product_sku' => (string) $product->sku,
                    'variant_name' => $variantNameEn ?: ($variantNameAr ?: null),
                    'variant_name_ar' => $variantNameAr ?: null,
                    'variant_sku' => $variant?->sku ?: null,
                    'quantity' => $quantity,
                    'unit_price' => $pricing['unit_price'],
                    'line_discount' => 0,
                    'is_gift' => false,
                    'note' => $this->resolveItemNote($data, $product->id, $variant?->id),
                ]);
            }

            if ($giftProductId) {
                $giftProduct = Product::query()->findOrFail($giftProductId);
                $this->assertStockAvailable($giftProduct, null, 1);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $giftProduct->id,
                    'variant_id' => null,
                    'product_name' => $giftProduct->getTranslation('name', 'en', false) ?: $giftProduct->sku,
                    'product_name_ar' => $giftProduct->getTranslation('name', 'ar', false),
                    'product_slug' => (string) $giftProduct->slug,
                    'product_sku' => (string) $giftProduct->sku,
                    'variant_name' => null,
                    'variant_name_ar' => null,
                    'variant_sku' => null,
                    'quantity' => 1,
                    'unit_price' => 0,
                    'line_discount' => 0,
                    'is_gift' => true,
                ]);

                if ($giftProduct->tracksStock()) {
                    $this->deductStock($giftProduct, null, 1);
                }
            }

            if ($checkoutPayment['wallet_used'] > 0) {
                $this->deductWalletForOrder($user, $userId, $checkoutPayment['wallet_used'], $order->id);
            }

            $this->cartItems->clearCart($userId);

            $order = $order->load(['coupon', 'address', 'items.product', 'items.variant']);

            $this->notifications->notifyAdmins(new NewOrderForAdminNotification($order));

            return $order;
        });
    }

    protected function resolveDefaultShippingFee(): float
    {
        return round((float) setting('shipping_price_per_km', 0), 2);
    }

    protected function assertValidPaymentMethod(string $paymentMethod): void
    {
        if (! in_array($paymentMethod, self::PAYMENT_METHODS, true)) {
            throw ValidationException::withMessages([
                'payment_method' => [__('messages.The selected payment method is invalid.')],
            ]);
        }
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @return Collection<int, CartItem>
     */
    protected function validateAndCleanCartItems(int $userId, Collection $cartItems): Collection
    {
        $valid = new Collection;
        $removed = false;

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $variant = $cartItem->variant;

            if (! $product || ! $product->isPurchasable()) {
                if ($product) {
                    $this->cartItems->removeItem($userId, $product, $cartItem->variant_id);
                }
                $removed = true;

                continue;
            }

            if ($product->isVariable()) {
                if (! $variant || ! $variant->is_active || (int) $variant->stock <= 0) {
                    $this->cartItems->removeItem($userId, $product, $cartItem->variant_id);
                    $removed = true;

                    continue;
                }
            } elseif (! $product->hasStock()) {
                $this->cartItems->removeItem($userId, $product, $cartItem->variant_id);
                $removed = true;

                continue;
            }

            $valid->push($cartItem);
        }

        if ($removed && $valid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Some items were removed because they are no longer available. Please review your cart and try again.'],
            ]);
        }

        return $valid;
    }

    /**
     * @return array{wallet_used: float, total: float, payment_status: string, paid_at: Carbon|null}
     */
    protected function resolveCheckoutPayment(
        string $paymentMethod,
        bool $useWallet,
        User $user,
        float $totalBeforeWallet,
    ): array {
        if ($paymentMethod === 'wallet') {
            return $this->resolveWalletOnlyPayment($user, $totalBeforeWallet);
        }

        if ($paymentMethod === 'cash_on_delivery') {
            return [
                'wallet_used' => 0.0,
                'total' => $totalBeforeWallet,
                'payment_status' => 'pending',
                'paid_at' => null,
            ];
        }

        return $this->resolveOptionalWalletPayment($useWallet, $user, $totalBeforeWallet);
    }

    /**
     * @return array{wallet_used: float, total: float, payment_status: string, paid_at: Carbon|null}
     */
    protected function resolveWalletOnlyPayment(User $user, float $totalBeforeWallet): array
    {
        if ($totalBeforeWallet <= 0) {
            return [
                'wallet_used' => 0.0,
                'total' => 0.0,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ];
        }

        $balance = (float) $user->wallet;

        if ($balance < $totalBeforeWallet) {
            throw ValidationException::withMessages([
                'wallet' => [__('messages.Insufficient wallet balance.')],
            ]);
        }

        return [
            'wallet_used' => $totalBeforeWallet,
            'total' => 0.0,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ];
    }

    /**
     * @return array{wallet_used: float, total: float, payment_status: string, paid_at: Carbon|null}
     */
    protected function resolveOptionalWalletPayment(bool $useWallet, User $user, float $totalBeforeWallet): array
    {
        $walletUsed = 0.0;

        if ($useWallet && $totalBeforeWallet > 0) {
            $balance = (float) $user->wallet;

            if ($balance <= 0) {
                throw ValidationException::withMessages([
                    'wallet' => [__('messages.Insufficient wallet balance.')],
                ]);
            }

            $walletUsed = round(min($balance, $totalBeforeWallet), 2);
        }

        $amountDue = round(max(0, $totalBeforeWallet - $walletUsed), 2);
        $isFullyPaidByWallet = $amountDue <= 0 && $walletUsed > 0;

        return [
            'wallet_used' => $walletUsed,
            'total' => $amountDue,
            'payment_status' => $isFullyPaidByWallet ? 'paid' : 'pending',
            'paid_at' => $isFullyPaidByWallet ? now() : null,
        ];
    }

    protected function deductWalletForOrder(User $user, int $userId, float $amount, int $orderId): void
    {
        $user->wallet = round((float) $user->wallet - $amount, 2);
        $user->save();

        WalletTransaction::query()->create([
            'user_id' => $userId,
            'type' => 'subtraction',
            'amount' => $amount,
            'balance_after' => $user->wallet,
            'notes' => 'Order #'.$orderId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveItemNote(array $data, int $productId, ?int $variantId): ?string
    {
        foreach ($data['item_notes'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if ((int) ($entry['product_id'] ?? 0) !== $productId) {
                continue;
            }

            $entryVariantId = array_key_exists('variant_id', $entry) && $entry['variant_id'] !== null
                ? (int) $entry['variant_id']
                : null;

            if ($entryVariantId !== $variantId) {
                continue;
            }

            $note = trim((string) ($entry['note'] ?? ''));

            return $note !== '' ? $note : null;
        }

        return null;
    }

    protected function buildAddressSnapshot(Address $address): array
    {
        return [
            'name' => $address->name,
            'phone' => $address->phone,
            'address' => $address->address,
            'city' => $address->city,
            'state' => $address->state,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
        ];
    }

    protected function resolveCheckoutCoupon(User $user, array $data, float $subTotal): ?Coupon
    {
        $coupon = null;

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::query()->where('code', strtoupper(trim((string) $data['coupon_code'])))->first();
        } else {
            $coupon = $this->cartItems->getAppliedCoupon($user->id);
        }

        if (! $coupon) {
            return null;
        }

        if (! $coupon->isValid()) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Coupon is invalid or expired.'],
            ]);
        }

        if ($subTotal < (float) $coupon->min_cart_amount) {
            throw ValidationException::withMessages([
                'coupon_code' => ['Cart total does not meet the minimum amount for this coupon.'],
            ]);
        }

        $usabilityError = $coupon->usabilityErrorForUser($user);

        if ($usabilityError) {
            throw ValidationException::withMessages([
                'coupon_code' => [$usabilityError],
            ]);
        }

        return $coupon;
    }

    protected function calculateCouponDiscount(?Coupon $coupon, float $subTotal): float
    {
        if (! $coupon) {
            return 0.0;
        }

        return $coupon->calculateDiscount($subTotal);
    }

    protected function calculateUnitPrice(
        Product $product,
        ?ProductVariant $variant = null,
        ?ProductUnit $productUnit = null,
    ): float {
        $unitPrice = (float) ($variant?->price ?? $productUnit?->price ?? $product->price);
        $discount = (float) ($product->discount ?? 0);
        $discountType = $product->discount_type;

        if ($discount > 0) {
            if ($discountType === 'percentage') {
                $unitPrice -= ($unitPrice * $discount) / 100;
            } else {
                $unitPrice = max(0, $unitPrice - $discount);
            }
        }

        return round($unitPrice, 2);
    }

    protected function assertStockAvailable(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): void {
        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages([
                'cart' => ['This product is not available for purchase.'],
            ]);
        }

        $stockTarget = $this->resolveStockTarget($product, $variant, $variantId, $productUnitId);

        if (! $stockTarget) {
            throw ValidationException::withMessages([
                'cart' => ['Selected variant is unavailable.'],
            ]);
        }

        if (! $stockTarget->tracksStock()) {
            if (! $stockTarget->isInStock()) {
                $name = $this->stockItemName($product, $variant);

                throw ValidationException::withMessages([
                    'cart' => ["{$name} is currently out of stock."],
                ]);
            }

            return;
        }

        $available = (int) $stockTarget->stock;

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'cart' => ['Insufficient stock for '.$this->stockItemName($product, $variant).'.'],
            ]);
        }
    }

    protected function resolveStockTarget(
        Product $product,
        ?ProductVariant $variant,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): Product|ProductVariant|ProductUnit|null {
        if ($product->isVariable()) {
            if ($productUnitId) {
                $productUnit = ProductUnit::query()
                    ->where('product_id', $product->id)
                    ->whereKey($productUnitId)
                    ->first();

                if ($productUnit) {
                    return $productUnit;
                }
            }

            return $this->resolveProductVariant($product, $variant, $variantId);
        }

        if ($productUnitId) {
            return ProductUnit::query()
                ->where('product_id', $product->id)
                ->whereKey($productUnitId)
                ->first();
        }

        return ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    protected function stockItemName(Product $product, ?ProductVariant $variant = null): string
    {
        if ($variant) {
            return $variant->getTranslation('name', app()->getLocale(), false)
                ?: $variant->getTranslation('name', 'en', false)
                ?: $variant->sku;
        }

        return $product->getTranslation('name', app()->getLocale(), false)
            ?: $product->getTranslation('name', 'en', false)
            ?: $product->sku;
    }

    protected function deductStockForOrderItems(Order $order): bool
    {
        $deducted = false;

        foreach ($order->items as $item) {
            if ($item->is_gift) {
                continue;
            }

            $product = $item->product;

            if (! $product) {
                continue;
            }

            if ($this->deductStock(
                $product,
                $item->variant,
                (int) $item->quantity,
                $item->variant_id,
                $item->product_unit_id,
            )) {
                $deducted = true;
            }
        }

        return $deducted;
    }

    protected function restoreStockForOrderItems(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->is_gift) {
                continue;
            }

            $product = $item->product;

            if (! $product) {
                continue;
            }

            $this->restoreStock(
                $product,
                $item->variant,
                (int) $item->quantity,
                $item->variant_id,
                $item->product_unit_id,
            );
        }
    }

    protected function deductStock(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): bool {
        if ($quantity <= 0) {
            return false;
        }

        if ($product->isVariable()) {
            if ($productUnitId) {
                $productUnit = ProductUnit::query()
                    ->where('product_id', $product->id)
                    ->whereKey($productUnitId)
                    ->lockForUpdate()
                    ->first();

                if (! $productUnit) {
                    throw ValidationException::withMessages([
                        'order' => ["Missing product unit for order item (product #{$product->id})."],
                    ]);
                }

                if (! $productUnit->tracksStock()) {
                    return false;
                }

                $productUnit->decrement('stock', $quantity);

                return true;
            }

            $variant = $this->resolveProductVariant($product, $variant, $variantId, lock: true);

            if (! $variant) {
                throw ValidationException::withMessages([
                    'order' => ["Missing product variant for order item (product #{$product->id})."],
                ]);
            }

            if (! $variant->tracksStock()) {
                return false;
            }

            $variant->decrement('stock', $quantity);

            return true;
        }

        $productUnit = $productUnitId
            ? ProductUnit::query()->where('product_id', $product->id)->whereKey($productUnitId)->lockForUpdate()->first()
            : null;

        if (! $productUnit) {
            throw ValidationException::withMessages([
                'order' => ["Missing product unit for order item (product #{$product->id})."],
            ]);
        }

        if (! $productUnit->tracksStock()) {
            return false;
        }

        $productUnit->decrement('stock', $quantity);

        return true;
    }

    protected function restoreStock(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        ?int $variantId = null,
        ?int $productUnitId = null,
    ): void {
        if ($quantity <= 0) {
            return;
        }

        if ($product->isVariable()) {
            if ($productUnitId) {
                $productUnit = ProductUnit::query()
                    ->where('product_id', $product->id)
                    ->whereKey($productUnitId)
                    ->lockForUpdate()
                    ->first();

                if ($productUnit && $productUnit->tracksStock()) {
                    $productUnit->increment('stock', $quantity);
                }

                return;
            }

            $variant = $this->resolveProductVariant($product, $variant, $variantId, lock: true);

            if (! $variant || ! $variant->tracksStock()) {
                return;
            }

            $variant->increment('stock', $quantity);

            return;
        }

        $productUnit = $productUnitId
            ? ProductUnit::query()->where('product_id', $product->id)->whereKey($productUnitId)->lockForUpdate()->first()
            : null;

        if ($productUnit && $productUnit->tracksStock()) {
            $productUnit->increment('stock', $quantity);
        }
    }

    protected function resolveProductVariant(
        Product $product,
        ?ProductVariant $variant,
        ?int $variantId,
        bool $lock = false,
    ): ?ProductVariant {
        if ($variant && (int) $variant->product_id === (int) $product->id) {
            if ($lock) {
                return ProductVariant::query()
                    ->whereKey($variant->id)
                    ->lockForUpdate()
                    ->first();
            }

            return $variant;
        }

        if (! $variantId) {
            return null;
        }

        $query = ProductVariant::query()
            ->where('product_id', $product->id)
            ->whereKey($variantId);

        return $lock ? $query->lockForUpdate()->first() : $query->first();
    }

    protected function syncVariableProductStock(Product $product): void
    {
        // Variant stock is stored on product_variants; product has no stock column.
    }

    public function update(Order $order, array $data): bool
    {
        return $this->orders->update($order, $data);
    }

    public function updateOrderPaymentStatus(Order $order, string $paymentStatus, ?int $actorUserId = null): Order
    {
        $fromPaymentStatus = $order->payment_status;

        if ($fromPaymentStatus === $paymentStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $paymentStatus, $fromPaymentStatus, $actorUserId) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            $updates = ['payment_status' => $paymentStatus];

            if ($paymentStatus === 'paid' && $order->paid_at === null) {
                $updates['paid_at'] = now();
            }

            $this->orders->update($order, $updates);

            $this->logOrderEvent(
                $order,
                'payment_change',
                $fromPaymentStatus,
                $paymentStatus,
                $actorUserId ?? auth()->id(),
            );

            return $order->fresh([
                'user',
                'coupon',
                'address',
                'items.product',
                'items.variant',
                'logs.user',
            ]);
        });
    }

    public function updateOrderStatus(Order $order, string $status, ?int $actorUserId = null): Order
    {
        $fromStatus = $order->status;

        if ($fromStatus === $status) {
            return $order;
        }

        return DB::transaction(function () use ($order, $status, $fromStatus, $actorUserId) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);
            $order->loadMissing('items.product', 'items.variant');

            $actorUserId = $actorUserId ?? auth()->id();
            $updates = ['status' => $status];
            $shouldLogPaymentOnDelivery = false;
            $fromPaymentStatus = $order->payment_status;

            if ($status === 'delivered') {
                $paymentUpdates = $this->paymentUpdatesForDeliveredOrder($order);

                if ($paymentUpdates !== []) {
                    $updates = array_merge($updates, $paymentUpdates);
                    $shouldLogPaymentOnDelivery = ! in_array($fromPaymentStatus, ['paid', 'refunded'], true);
                }

                if ($order->stock_deducted_at === null) {
                    $deducted = $this->deductStockForOrderItems($order);

                    if ($deducted) {
                        $updates['stock_deducted_at'] = now();
                    }
                }
            }

            if (in_array($status, ['cancelled', 'refunded'], true) && $order->stock_deducted_at !== null) {
                $this->restoreStockForOrderItems($order);
                $updates['stock_deducted_at'] = null;
            }

            $this->orders->update($order, $updates);

            $this->logOrderEvent($order, 'status_change', $fromStatus, $status, $actorUserId);

            if ($shouldLogPaymentOnDelivery) {
                $this->logOrderEvent($order, 'payment_change', $fromPaymentStatus, 'paid', $actorUserId, [
                    'trigger' => 'delivered',
                    'payment_method' => $updates['payment_method'] ?? $order->payment_method,
                ]);
            }

            $order = $order->fresh([
                'user',
                'coupon',
                'address',
                'items.product',
                'items.variant',
                'logs.user',
            ]);

            // Notify Customer
            if ($order->user) {
                $order->user->notify(new \App\Notifications\OrderStatusUpdatedNotification($order));
            }

            if ($status === 'delivered') {
                $this->points->awardCashbackForDeliveredOrder($order);
                $this->goals->evaluateGoalsForDeliveredOrder($order);
                $this->notifications->notifyAdmins(new \App\Notifications\OrderDeliveredAdminNotification($order));
            }

            return $order;
        });
    }

    public function delete(Order $order): bool
    {
        return $this->orders->delete($order);
    }

    public function forceDelete(Order $order): bool
    {
        return $this->orders->forceDelete($order);
    }

    public function restore(Order $order): bool
    {
        return $this->orders->restore($order);
    }

    public function cancelOrderForUser(int $orderId, int $userId): ?Order
    {
        return DB::transaction(function () use ($orderId, $userId) {
            $order = $this->getOrderByIdForUser($orderId, $userId);

            if (! $order) {
                return null;
            }

            if (! in_array($order->status, ['pending', 'processing'], true)) {
                throw ValidationException::withMessages([
                    'order' => ['Order cannot be cancelled at this stage.'],
                ]);
            }

            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $walletUsed = (float) $order->wallet_used;
            $isPaid = $order->payment_status === 'paid';
            $refundAmount = $isPaid ? (float) $order->total : $walletUsed;

            if ($refundAmount > 0) {
                $user->wallet = round((float) $user->wallet + $refundAmount, 2);
                $user->save();

                WalletTransaction::query()->create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => $refundAmount,
                    'balance_after' => $user->wallet,
                    'notes' => 'Refund for cancelled order #'.$order->id,
                ]);
            }

            $order->loadMissing('items.product', 'items.variant');

            $fromStatus = $order->status;
            $fromPaymentStatus = $order->payment_status;
            $updates = ['status' => 'cancelled'];

            if ($isPaid) {
                $updates['payment_status'] = 'refunded';
                $updates['refund_status'] = 'refunded';
                $updates['refunded_total'] = $refundAmount;
            }

            if ($order->stock_deducted_at !== null) {
                $this->restoreStockForOrderItems($order);
                $updates['stock_deducted_at'] = null;
            }

            $this->orders->update($order, $updates);

            $this->logOrderEvent($order, 'cancelled', $fromStatus, 'cancelled', $userId);

            if ($isPaid) {
                $this->logOrderEvent(
                    $order,
                    'payment_change',
                    $fromPaymentStatus,
                    'refunded',
                    $userId,
                    ['refund_amount' => $refundAmount],
                );
            }

            return $order->fresh(['coupon', 'address', 'items.product', 'items.variant', 'logs.user']);
        });
    }

    public function refundOrder(Order $order, int $userId): Order
    {
        return DB::transaction(function () use ($order, $userId) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->refund_status === 'refunded') {
                return $order->fresh(['coupon', 'address', 'items.product', 'items.variant']);
            }

            if ($order->user_id !== $userId) {
                throw ValidationException::withMessages([
                    'order' => ['You are not allowed to refund this order.'],
                ]);
            }

            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $walletUsed = (float) $order->wallet_used;
            $isPaid = $order->payment_status === 'paid';
            $refundAmount = $isPaid
                ? round((float) $order->total + $walletUsed, 2)
                : round($walletUsed, 2);

            if ($refundAmount > 0) {
                $user->wallet = round((float) $user->wallet + $refundAmount, 2);
                $user->save();

                WalletTransaction::query()->create([
                    'user_id' => $userId,
                    'type' => 'addition',
                    'amount' => $refundAmount,
                    'balance_after' => $user->wallet,
                    'notes' => 'Refund for order #'.$order->id,
                ]);
            }

            $order->loadMissing('items.product', 'items.variant');

            $fromStatus = $order->status;
            $fromPaymentStatus = $order->payment_status;
            $updates = [
                'status' => 'refunded',
                'refund_status' => 'refunded',
                'refunded_total' => $refundAmount,
            ];

            if ($isPaid) {
                $updates['payment_status'] = 'refunded';
            }

            if ($order->stock_deducted_at !== null) {
                $this->restoreStockForOrderItems($order);
                $updates['stock_deducted_at'] = null;
            }

            $this->orders->update($order, $updates);

            $this->logOrderEvent($order, 'refunded', $fromStatus, 'refunded', $userId, [
                'refund_amount' => $refundAmount,
            ]);

            if ($isPaid && $fromPaymentStatus !== 'refunded') {
                $this->logOrderEvent($order, 'payment_change', $fromPaymentStatus, 'refunded', $userId);
            }

            return $order->fresh(['coupon', 'address', 'items.product', 'items.variant', 'logs.user']);
        });
    }

    public function payOrderForUser(int $orderId, int $userId, string $paymentMethod): ?Order
    {
        $order = $this->getOrderByIdForUser($orderId, $userId);

        if (! $order) {
            return null;
        }

        $paymentMethod = strtolower(trim($paymentMethod));
        $this->assertValidPaymentMethod($paymentMethod);

        return DB::transaction(function () use ($order, $userId, $paymentMethod) {
            if ($order->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'order' => ['Order is already paid.'],
                ]);
            }

            if ($order->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'order' => ['Cancelled orders cannot be paid.'],
                ]);
            }

            $fromPaymentStatus = $order->payment_status;

            $this->orders->update($order, [
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
            ]);

            $this->logOrderEvent($order, 'payment_change', $fromPaymentStatus, 'paid', $userId, [
                'payment_method' => $paymentMethod,
            ]);

            return $order->fresh(['coupon', 'address', 'items.product', 'items.variant', 'logs.user']);
        });
    }

    /**
     * When an order is delivered, treat collection of amount due as payment (e.g. cash on delivery).
     *
     * @return array<string, mixed>
     */
    protected function paymentUpdatesForDeliveredOrder(Order $order): array
    {
        if (in_array($order->payment_status, ['paid', 'refunded'], true)) {
            if ($order->payment_status === 'paid' && $order->paid_at === null) {
                return ['paid_at' => now()];
            }

            return [];
        }

        $updates = [
            'payment_status' => 'paid',
            'paid_at' => now(),
        ];

        if (blank($order->payment_method)) {
            $updates['payment_method'] = 'cash_on_delivery';
        }

        return $updates;
    }

    public function logOrderEvent(
        Order $order,
        string $type,
        ?string $fromStatus,
        ?string $toStatus,
        ?int $userId = null,
        array $payload = [],
    ): OrderLog {
        return OrderLog::query()->create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'type' => $type,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'payload' => $payload !== [] ? $payload : null,
        ]);
    }

    /**
     * @return array{added: list<array<string, mixed>>, skipped: list<array<string, mixed>>}
     */
    public function reorder(int $orderId, int $userId): array
    {
        $order = $this->getOrderByIdForUser($orderId, $userId);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => ['Order not found.'],
            ]);
        }

        $order->load(['items.product', 'items.variant']);

        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages([
                'order' => ['This order has no items to reorder.'],
            ]);
        }

        $added = [];
        $skipped = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product || ! $product->is_active) {
                $skipped[] = [
                    'product_id' => $item->product_id,
                    'reason' => 'Product is unavailable.',
                ];

                continue;
            }

            if ($product->isVariable() && ! $item->variant_id) {
                $skipped[] = [
                    'product_id' => $product->id,
                    'reason' => 'Variant is required for this product.',
                ];

                continue;
            }

            $this->cartItems->addOrIncrement(
                $userId,
                $product,
                $item->variant_id,
                (int) $item->quantity,
            );

            $added[] = [
                'product_id' => $product->id,
                'variant_id' => $item->variant_id,
                'quantity' => (int) $item->quantity,
            ];
        }

        return [
            'added' => $added,
            'skipped' => $skipped,
        ];
    }
}
