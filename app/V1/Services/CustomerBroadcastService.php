<?php

namespace App\V1\Services;

use App\Models\Coupon;
use App\Models\Offer;
use App\Models\User;
use App\Notifications\CouponPromotionNotification;
use App\Notifications\OfferPromotionNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CustomerBroadcastService
{
    public function notifyCustomersAboutOffer(Offer $offer): int
    {
        $this->assertOfferIsRunnable($offer);

        return $this->broadcast(new OfferPromotionNotification($offer));
    }

    public function notifyCustomersAboutCoupon(Coupon $coupon): int
    {
        $this->assertCouponIsRunnable($coupon);

        return $this->broadcast(new CouponPromotionNotification($coupon));
    }

    protected function assertOfferIsRunnable(Offer $offer): void
    {
        if ($offer->validityStatus() !== 'running') {
            throw ValidationException::withMessages([
                'offer' => [__('messages.Offer must be active and running to notify customers.')],
            ]);
        }
    }

    protected function assertCouponIsRunnable(Coupon $coupon): void
    {
        if ($coupon->validityStatus() !== 'running') {
            throw ValidationException::withMessages([
                'coupon' => [__('messages.Coupon must be active and running to notify customers.')],
            ]);
        }
    }

    protected function broadcast(Notification $notification): int
    {
        $sent = 0;

        User::query()
            ->where('role', 'user')
            ->where('is_active', true)
            ->orderBy('id')
            ->chunkById(100, function ($customers) use ($notification, &$sent): void {
                foreach ($customers as $customer) {
                    $customer->notify($notification);
                    $sent++;
                }
            });

        return $sent;
    }
}
