<?php

namespace Database\Seeders\Concerns;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\User;
use App\V1\Services\CheckoutSettingsService;
use RuntimeException;

trait BuildsRealisticOrders
{
    /**
     * @return array{name: string, address: string, latitude: string|null, longitude: string|null, phone: string|null}
     */
    protected function addressSnapshot(Address $address): array
    {
        return [
            'name' => $address->name,
            'address' => $address->address,
            'latitude' => $address->latitude,
            'longitude' => $address->longitude,
            'phone' => $address->phone,
        ];
    }

    protected function defaultProductUnit(Product $product): ?ProductUnit
    {
        return $product->productUnits()
            ->where('is_default', true)
            ->first()
            ?? $product->productUnits()->first();
    }

    protected function unitPriceForProduct(Product $product): float
    {
        $unit = $this->defaultProductUnit($product);

        return round((float) ($unit?->price ?? 0), 2);
    }

    /**
     * @param  array<string, int>  $skuQuantities
     * @return array{
     *     lines: list<array{product: Product, unit: ProductUnit, quantity: int, unit_price: float, line_subtotal: float, note: string}>,
     *     sub_total: float,
     *     line_count: int
     * }
     */
    protected function buildLineDrafts(array $skuQuantities, string $notePrefix = ''): array
    {
        $lines = [];
        $subTotal = 0.0;

        foreach ($skuQuantities as $sku => $quantity) {
            $product = Product::query()->where('sku', $sku)->first();
            $unit = $product ? $this->defaultProductUnit($product) : null;

            if (! $product || ! $unit) {
                continue;
            }

            $qty = max(1, (int) $quantity);
            $unitPrice = round((float) $unit->price, 2);
            $lineSubtotal = round($unitPrice * $qty, 2);

            $lines[] = [
                'product' => $product,
                'unit' => $unit,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
                'note' => $notePrefix !== '' ? "{$notePrefix} ({$sku})" : '',
            ];

            $subTotal += $lineSubtotal;
        }

        return [
            'lines' => $lines,
            'sub_total' => round($subTotal, 2),
            'line_count' => count($lines),
        ];
    }

    protected function assertSeededCartMeetsLimits(int $lineCount, float $subTotal): void
    {
        $checkout = app(CheckoutSettingsService::class);
        $minLines = $checkout->minLineCount();
        $minSubtotal = $checkout->minSubtotal();

        if ($minLines > 0 && $lineCount < $minLines) {
            throw new RuntimeException(
                "Seeded cart has {$lineCount} lines but cart_min_line_count is {$minLines}.",
            );
        }

        if ($minSubtotal > 0 && $subTotal < $minSubtotal) {
            throw new RuntimeException(
                "Seeded cart subtotal {$subTotal} is below cart_min_subtotal {$minSubtotal}.",
            );
        }
    }

    protected function couponDiscount(?Coupon $coupon, float $subTotal): float
    {
        if (! $coupon) {
            return 0.0;
        }

        if ($subTotal < (float) $coupon->min_cart_amount) {
            return 0.0;
        }

        return $coupon->calculateDiscount($subTotal);
    }

    /**
     * @return array{total_shipping: float, fast_shipping_fee: float, is_fast_shipping: bool, total: float}
     */
    protected function calculateOrderTotals(
        float $subTotal,
        float $couponDiscount = 0,
        float $orderDiscount = 0,
        bool $freeDelivery = false,
        float $walletUsed = 0,
    ): array {
        $checkout = app(CheckoutSettingsService::class);
        $shipping = $checkout->computeShipping(false, $freeDelivery);

        $total = round(
            max(0, $subTotal - $orderDiscount - $couponDiscount + $shipping['total_shipping'] - $walletUsed),
            2,
        );

        return [
            'total_shipping' => $shipping['total_shipping'],
            'fast_shipping_fee' => $shipping['fast_shipping_fee'],
            'is_fast_shipping' => $shipping['is_fast_shipping'],
            'total' => $total,
        ];
    }

    /**
     * @param  list<array{product: Product, unit: ProductUnit, quantity: int, unit_price: float, line_subtotal: float, note: string}>  $lines
     */
    protected function syncOrderItems(Order $order, array $lines): void
    {
        $productIds = collect($lines)->pluck('product.id')->all();

        OrderItem::query()
            ->where('order_id', $order->id)
            ->whereNotIn('product_id', $productIds)
            ->delete();

        foreach ($lines as $line) {
            $item = OrderItem::query()->firstOrNew([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'variant_id' => null,
            ]);

            $item->fill([
                'product_unit_id' => $line['unit']->id,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_discount' => 0,
                'is_gift' => false,
                'note' => $line['note'],
            ]);

            $item->fillMissingSnapshotFields();
            $item->save();
        }
    }

    /**
     * @param  array<string, int>  $skuQuantities
     */
    protected function seedOrderForCustomer(
        User $customer,
        Address $address,
        string $notes,
        array $skuQuantities,
        array $orderAttributes,
        ?Coupon $coupon = null,
    ): Order {
        $draft = $this->buildLineDrafts($skuQuantities);
        $this->assertSeededCartMeetsLimits($draft['line_count'], $draft['sub_total']);

        $couponDiscount = $this->couponDiscount($coupon, $draft['sub_total']);
        $freeDelivery = $coupon?->grantsFreeDelivery() ?? false;
        $totals = $this->calculateOrderTotals(
            $draft['sub_total'],
            $couponDiscount,
            (float) ($orderAttributes['order_discount'] ?? 0),
            $freeDelivery,
            (float) ($orderAttributes['wallet_used'] ?? 0),
        );

        $order = Order::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'notes' => $notes,
            ],
            array_merge([
                'branch_id' => 1,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $address->phone ?? $customer->phone,
                'sub_total' => $draft['sub_total'],
                'order_discount' => (float) ($orderAttributes['order_discount'] ?? 0),
                'coupon_id' => $coupon?->id,
                'coupon_discount' => $couponDiscount,
                'total_shipping' => $totals['total_shipping'],
                'wallet_used' => (float) ($orderAttributes['wallet_used'] ?? 0),
                'total' => $totals['total'],
                'payment_method' => 'cash_on_delivery',
                'refund_status' => 'none',
                'address_id' => $address->id,
                'shipping_address_snapshot' => $this->addressSnapshot($address),
                'shipping_day' => 'monday',
                'is_fast_shipping' => $totals['is_fast_shipping'],
                'fast_shipping_fee' => $totals['fast_shipping_fee'],
                'total_commission' => 0,
            ], $orderAttributes),
        );

        $this->syncOrderItems($order, $draft['lines']);

        return $order->fresh(['items']);
    }
}
