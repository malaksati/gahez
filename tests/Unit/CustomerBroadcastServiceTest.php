<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\OfferPromotionNotification;
use App\V1\Services\CustomerBroadcastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerBroadcastServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_offer_notification_to_active_customers(): void
    {
        Notification::fake();

        User::factory()->create(['role' => 'user', 'is_active' => true, 'email' => 'c1@test.com']);
        User::factory()->create(['role' => 'user', 'is_active' => false, 'email' => 'c2@test.com']);
        User::factory()->create(['role' => 'admin', 'is_active' => true, 'email' => 'admin@test.com']);

        $offer = Offer::query()->create([
            'name' => ['en' => 'Summer sale', 'ar' => 'تخفيضات'],
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
        ]);

        $sent = app(CustomerBroadcastService::class)->notifyCustomersAboutOffer($offer);

        $this->assertSame(1, $sent);
        Notification::assertSentTimes(OfferPromotionNotification::class, 1);
    }

    public function test_rejects_inactive_offer_broadcast(): void
    {
        $offer = Offer::query()->create([
            'name' => ['en' => 'Expired', 'ar' => 'منتهي'],
            'type' => 'percentage',
            'value' => 10,
            'is_active' => false,
        ]);

        $this->expectException(ValidationException::class);

        app(CustomerBroadcastService::class)->notifyCustomersAboutOffer($offer);
    }

    public function test_sends_coupon_notification_to_active_customers(): void
    {
        Notification::fake();

        User::factory()->create(['role' => 'user', 'is_active' => true, 'email' => 'buyer@test.com']);

        $coupon = Coupon::query()->create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'discount_value' => 10,
            'min_cart_amount' => 0,
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
        ]);

        $sent = app(CustomerBroadcastService::class)->notifyCustomersAboutCoupon($coupon);

        $this->assertSame(1, $sent);
    }
}
