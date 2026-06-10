<?php

namespace App\V1\Services;

use Illuminate\Validation\ValidationException;

class CheckoutSettingsService
{
    public const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function baseShippingFee(): float
    {
        return round((float) setting('shipping_price_per_km', 0), 2);
    }

    public function fastShippingExtraFee(): float
    {
        return round((float) setting('fast_shipping_fee', 0), 2);
    }

    public function minLineCount(): int
    {
        return max(0, (int) setting('cart_min_line_count', 0));
    }

    public function minSubtotal(): float
    {
        return round(max(0, (float) setting('cart_min_subtotal', 0)), 2);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function weekdays(): array
    {
        return collect(self::WEEKDAYS)
            ->map(fn (string $day) => [
                'value' => $day,
                'label' => __('messages.weekday_'.$day),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function shippingPayload(bool $qualifiesForFreeDelivery = false): array
    {
        $base = $qualifiesForFreeDelivery ? 0.0 : $this->baseShippingFee();
        $extra = $qualifiesForFreeDelivery ? 0.0 : $this->fastShippingExtraFee();

        return [
            'weekdays' => $this->weekdays(),
            'base_fee' => $base,
            'fast_shipping_extra_fee' => $extra,
            'options' => [
                ['type' => 'standard', 'total_fee' => $base],
                ['type' => 'fast', 'total_fee' => round($base + $extra, 2)],
            ],
        ];
    }

    /**
     * @return array{total_shipping: float, fast_shipping_fee: float, is_fast_shipping: bool}
     */
    public function computeShipping(bool $isFastShipping, bool $freeDelivery = false): array
    {
        $base = $freeDelivery ? 0.0 : $this->baseShippingFee();
        $extra = ($isFastShipping && ! $freeDelivery) ? $this->fastShippingExtraFee() : 0.0;

        return [
            'total_shipping' => round($base + $extra, 2),
            'fast_shipping_fee' => $extra,
            'is_fast_shipping' => $isFastShipping,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cartLimits(float $subtotal, int $lineCount): array
    {
        $minLines = $this->minLineCount();
        $minSubtotal = $this->minSubtotal();
        $meetsLines = $minLines === 0 || $lineCount >= $minLines;
        $meetsSubtotal = $minSubtotal === 0.0 || $subtotal >= $minSubtotal;

        return [
            'line_count' => $lineCount,
            'min_line_count' => $minLines,
            'meets_line_minimum' => $meetsLines,
            'subtotal' => round($subtotal, 2),
            'min_subtotal' => $minSubtotal,
            'meets_subtotal_minimum' => $meetsSubtotal,
            'can_checkout' => $lineCount > 0 && $meetsLines && $meetsSubtotal,
        ];
    }

    public function assertCartLimits(int $lineCount, float $subtotal): void
    {
        $minLines = $this->minLineCount();
        $minSubtotal = $this->minSubtotal();

        if ($minLines > 0 && $lineCount < $minLines) {
            throw ValidationException::withMessages([
                'cart' => [__('messages.Cart minimum line count', ['count' => $minLines])],
            ]);
        }

        if ($minSubtotal > 0 && $subtotal < $minSubtotal) {
            throw ValidationException::withMessages([
                'cart' => [__('messages.Cart minimum subtotal', ['amount' => $minSubtotal])],
            ]);
        }
    }

    public function assertValidShippingDay(?string $day): string
    {
        $day = strtolower(trim((string) $day));

        if (! in_array($day, self::WEEKDAYS, true)) {
            throw ValidationException::withMessages([
                'shipping_day' => [__('messages.Invalid shipping day.')],
            ]);
        }

        return $day;
    }
}
