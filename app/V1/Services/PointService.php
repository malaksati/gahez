<?php

namespace App\V1\Services;

use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class PointService
{
    public function orderQualifiesForCashback(Order $order): bool
    {
        if ((float) $order->order_discount > 0) {
            return false;
        }

        if ($order->gift_offer_id) {
            return false;
        }

        if ($order->relationLoaded('items')) {
            if ($order->items->contains(fn ($item) => $item->is_gift)) {
                return false;
            }
        } elseif ($order->items()->where('is_gift', true)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Award cashback points and wallet credit when an order is delivered.
     */
    public function awardCashbackForDeliveredOrder(Order $order): bool
    {
        if ($order->cashback_awarded_at !== null) {
            return false;
        }

        if (! $this->orderQualifiesForCashback($order)) {
            return false;
        }

        $cashbackPercentage = (float) setting('cashback_percentage', 0);
        $pointToValue = (float) setting('point_to_value', 0);

        if ($cashbackPercentage <= 0 || $pointToValue <= 0) {
            return false;
        }

        $cartTotal = (float) $order->sub_total;
        if ($cartTotal <= 0) {
            return false;
        }

        $cashbackAmount = round($cartTotal * ($cashbackPercentage / 100), 2);
        if ($cashbackAmount <= 0) {
            return false;
        }

        $points = (int) floor($cashbackAmount / $pointToValue);
        if ($points <= 0) {
            return false;
        }

        $walletCredit = round($points * $pointToValue, 2);
        $user = $order->user;

        if (! $user) {
            return false;
        }

        return DB::transaction(function () use ($order, $user, $points, $walletCredit, $cashbackPercentage, $pointToValue) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->cashback_awarded_at !== null) {
                return false;
            }

            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            $pointsAfter = (int) $lockedUser->points + $points;
            $walletAfter = round((float) $lockedUser->wallet + $walletCredit, 2);

            $lockedUser->update([
                'points' => $pointsAfter,
                'wallet' => $walletAfter,
            ]);

            PointTransaction::query()->create([
                'user_id' => $lockedUser->id,
                'type' => 'addition',
                'amount' => $points,
                'balance_after' => $pointsAfter,
                'notes' => __('messages.Cashback for order #:id (:percent% of :total)', [
                    'id' => $lockedOrder->id,
                    'percent' => rtrim(rtrim(format_local_number($cashbackPercentage, 2), '0'), '.'),
                    'total' => format_local_number((float) $lockedOrder->sub_total, 2).' '.display_currency(),
                ]),
            ]);

            WalletTransaction::query()->create([
                'user_id' => $lockedUser->id,
                'type' => 'addition',
                'amount' => $walletCredit,
                'balance_after' => $walletAfter,
                'notes' => __('messages.Cashback wallet credit for order #:id (:points pts × :rate :currency)', [
                    'id' => $lockedOrder->id,
                    'points' => $points,
                    'rate' => format_local_number($pointToValue, 2),
                    'currency' => display_currency(),
                ]),
            ]);

            $lockedOrder->update(['cashback_awarded_at' => now()]);

            return true;
        });
    }
}
