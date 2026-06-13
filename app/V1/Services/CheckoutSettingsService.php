<?php

namespace App\V1\Services;

use App\Models\Branch;
use App\V1\Support\GeoDistance;
use Illuminate\Support\Carbon;
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

    public function usesDistanceBasedShipping(): bool
    {
        return $this->standardShippingFee() === null;
    }

    public function standardShippingFee(): ?float
    {
        $value = setting('standard_shipping_fee');

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    public function shippingPricePerKm(): float
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

    public function todayWeekday(): string
    {
        return strtolower(Carbon::now()->englishDayOfWeek);
    }

    public function resolveCheckoutBranch(?int $branchId = null): Branch
    {
        if ($branchId) {
            $branch = Branch::query()
                ->where('is_active', true)
                ->find($branchId);

            if ($branch) {
                return $branch;
            }
        }

        $branch = Branch::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($branch) {
            return $branch;
        }

        return Branch::query()->orderBy('id')->firstOrFail();
    }

  /**
     * @return list<array{value: string, label: string}>
     */
    public function weekdays(): array
    {
        return $this->mapWeekdays(self::WEEKDAYS);
    }

    /**
     * Standard shipping: any weekday except today.
     *
     * @return list<array{value: string, label: string}>
     */
    public function standardWeekdays(): array
    {
        $today = $this->todayWeekday();

        return $this->mapWeekdays(
            array_values(array_filter(self::WEEKDAYS, fn (string $day) => $day !== $today)),
        );
    }

    /**
     * Fast shipping: today only (same-day delivery).
     *
     * @return list<array{value: string, label: string}>
     */
    public function fastWeekdays(): array
    {
        return $this->mapWeekdays([$this->todayWeekday()]);
    }

    /**
     * @param  list<string>  $days
     * @return list<array{value: string, label: string}>
     */
    protected function mapWeekdays(array $days): array
    {
        return collect($days)
            ->map(fn (string $day) => [
                'value' => $day,
                'label' => __('messages.weekday_'.$day),
            ])
            ->all();
    }

    public function baseShippingFee(
        mixed $addressLatitude = null,
        mixed $addressLongitude = null,
        ?int $branchId = null,
    ): float {
        $standardFee = $this->standardShippingFee();

        if ($standardFee !== null) {
            return $standardFee;
        }

        return $this->distanceBasedShippingFee($addressLatitude, $addressLongitude, $branchId);
    }

    public function distanceBasedShippingFee(
        mixed $addressLatitude = null,
        mixed $addressLongitude = null,
        ?int $branchId = null,
    ): float {
        $branch = $this->resolveCheckoutBranch($branchId);
        $distanceKm = $this->distanceKmToAddress($branch, $addressLatitude, $addressLongitude);

        if ($distanceKm === null) {
            return 0.0;
        }

        return round($distanceKm * $this->shippingPricePerKm(), 2);
    }

    public function distanceKmToAddress(
        Branch $branch,
        mixed $addressLatitude = null,
        mixed $addressLongitude = null,
    ): ?float {
        return GeoDistance::kilometers(
            GeoDistance::parseLatitude($branch->latitude),
            GeoDistance::parseLongitude($branch->longitude),
            GeoDistance::parseLatitude($addressLatitude),
            GeoDistance::parseLongitude($addressLongitude),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function shippingPayload(
        bool $qualifiesForFreeDelivery = false,
        mixed $addressLatitude = null,
        mixed $addressLongitude = null,
        ?int $branchId = null,
    ): array {
        $configuredBase = $this->baseShippingFee($addressLatitude, $addressLongitude, $branchId);
        $configuredExtra = $this->fastShippingExtraFee();
        $effectiveBase = $qualifiesForFreeDelivery ? 0.0 : $configuredBase;
        $effectiveExtra = $qualifiesForFreeDelivery ? 0.0 : $configuredExtra;
        $standardWeekdays = $this->standardWeekdays();
        $fastWeekdays = $this->fastWeekdays();
        $branch = $this->resolveCheckoutBranch($branchId);
        $distanceKm = $this->distanceKmToAddress($branch, $addressLatitude, $addressLongitude);

        return [
            'weekdays' => $standardWeekdays,
            'base_fee' => $configuredBase,
            'fast_shipping_extra_fee' => $configuredExtra,
            'free_delivery_applied' => $qualifiesForFreeDelivery,
            'distance_based' => $this->usesDistanceBasedShipping(),
            'shipping_price_per_km' => $this->shippingPricePerKm(),
            'distance_km' => $distanceKm,
            'branch_id' => $branch->id,
            'options' => [
                [
                    'type' => 'standard',
                    'weekdays' => $standardWeekdays,
                    'total_fee' => $effectiveBase,
                ],
                [
                    'type' => 'fast',
                    'weekdays' => $fastWeekdays,
                    'total_fee' => round($effectiveBase + $effectiveExtra, 2),
                ],
            ],
        ];
    }

    /**
     * @return array{total_shipping: float, fast_shipping_fee: float, is_fast_shipping: bool, distance_km: float|null, branch_id: int}
     */
    public function computeShipping(
        bool $isFastShipping,
        bool $freeDelivery = false,
        mixed $addressLatitude = null,
        mixed $addressLongitude = null,
        ?int $branchId = null,
    ): array {
        $branch = $this->resolveCheckoutBranch($branchId);
        $distanceKm = $this->distanceKmToAddress($branch, $addressLatitude, $addressLongitude);
        $base = $freeDelivery ? 0.0 : $this->baseShippingFee($addressLatitude, $addressLongitude, $branch->id);
        $extra = ($isFastShipping && ! $freeDelivery) ? $this->fastShippingExtraFee() : 0.0;

        return [
            'total_shipping' => round($base + $extra, 2),
            'fast_shipping_fee' => $extra,
            'is_fast_shipping' => $isFastShipping,
            'distance_km' => $distanceKm,
            'branch_id' => $branch->id,
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

    public function assertValidShippingDayForCheckout(string $day, bool $isFastShipping): string
    {
        $day = $this->assertValidShippingDay($day);
        $today = $this->todayWeekday();

        if ($isFastShipping && $day !== $today) {
            throw ValidationException::withMessages([
                'shipping_day' => [__('messages.Fast shipping requires today as shipping day', [
                    'day' => __('messages.weekday_'.$today),
                ])],
            ]);
        }

        if (! $isFastShipping && $day === $today) {
            throw ValidationException::withMessages([
                'shipping_day' => [__('messages.Standard shipping cannot use today as shipping day')],
            ]);
        }

        return $day;
    }
}
