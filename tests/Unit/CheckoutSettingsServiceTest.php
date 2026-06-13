<?php

namespace Tests\Unit;

use App\Models\Branch;
use App\Models\Setting;
use App\V1\Services\CheckoutSettingsService;
use App\V1\Support\GeoDistance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CheckoutSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Branch::query()->create([
            'name' => ['en' => 'Main', 'ar' => 'رئيسي'],
            'address' => 'Branch',
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'is_active' => true,
        ]);
    }

    public function test_shipping_payload_returns_configured_fees_even_when_free_delivery_applies(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'standard_shipping_fee'],
            ['value' => '5', 'type' => 'number'],
        );
        setting_forget('standard_shipping_fee');

        Setting::query()->updateOrCreate(
            ['key' => 'fast_shipping_fee'],
            ['value' => '10', 'type' => 'number'],
        );
        setting_forget('fast_shipping_fee');

        $payload = app(CheckoutSettingsService::class)->shippingPayload(true);

        $this->assertSame(5.0, $payload['base_fee']);
        $this->assertSame(10.0, $payload['fast_shipping_extra_fee']);
        $this->assertTrue($payload['free_delivery_applied']);
        $this->assertFalse($payload['distance_based']);
        $this->assertSame(0.0, $payload['options'][0]['total_fee']);
        $this->assertSame(0.0, $payload['options'][1]['total_fee']);
    }

    public function test_distance_based_shipping_uses_branch_and_address_coordinates(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'standard_shipping_fee'],
            ['value' => '', 'type' => 'number'],
        );
        setting_forget('standard_shipping_fee');

        Setting::query()->updateOrCreate(
            ['key' => 'shipping_price_per_km'],
            ['value' => '2', 'type' => 'number'],
        );
        setting_forget('shipping_price_per_km');

        $branch = Branch::query()->create([
            'name' => ['en' => 'Branch', 'ar' => 'فرع'],
            'address' => 'Branch address',
            'latitude' => '30.0444',
            'longitude' => '31.2357',
            'is_active' => true,
        ]);

        $service = app(CheckoutSettingsService::class);
        $distanceKm = GeoDistance::kilometers(30.0444, 31.2357, 30.0561, 31.3300);

        $this->assertNotNull($distanceKm);

        $breakdown = $service->computeShipping(false, false, '30.0561', '31.3300', $branch->id);

        $this->assertSame($branch->id, $breakdown['branch_id']);
        $this->assertSame($distanceKm, $breakdown['distance_km']);
        $this->assertSame(round($distanceKm * 2, 2), $breakdown['total_shipping']);
    }

    public function test_standard_weekdays_exclude_today_and_fast_only_includes_today(): void
    {
        Carbon::setTestNow('2026-06-10 12:00:00');

        $service = app(CheckoutSettingsService::class);
        $payload = $service->shippingPayload(false);

        $standardValues = collect($payload['options'][0]['weekdays'])->pluck('value')->all();
        $fastValues = collect($payload['options'][1]['weekdays'])->pluck('value')->all();

        $this->assertCount(6, $standardValues);
        $this->assertNotContains('wednesday', $standardValues);
        $this->assertSame(['wednesday'], $fastValues);
    }

    public function test_assert_valid_shipping_day_for_checkout_enforces_fast_and_standard_rules(): void
    {
        Carbon::setTestNow('2026-06-10 12:00:00');

        $service = app(CheckoutSettingsService::class);

        $this->assertSame('wednesday', $service->assertValidShippingDayForCheckout('wednesday', true));
        $this->assertSame('monday', $service->assertValidShippingDayForCheckout('monday', false));

        try {
            $service->assertValidShippingDayForCheckout('thursday', true);
            $this->fail('Expected ValidationException for fast shipping on wrong day.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('shipping_day', $exception->errors());
        }

        try {
            $service->assertValidShippingDayForCheckout('wednesday', false);
            $this->fail('Expected ValidationException for standard shipping on today.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('shipping_day', $exception->errors());
        }
    }
}
