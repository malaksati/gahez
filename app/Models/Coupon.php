<?php

namespace App\Models;

use App\Models\Concerns\HasValidityPeriod;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasValidityPeriod;

    protected $fillable = [
        'code',
        'type',
        'discount_value',
        'min_cart_amount',
        'usage_limit_per_user',
        'usage_limit',
        'first_order_only',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function userOrderCount(User $user): int
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();
    }

    public function ordersUsedCountByUser(User $user): int
    {
        return $this->orders()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();
    }

    public function totalOrdersUsed(): int
    {
        return $this->orders()
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->count();
    }

    public function grantsFreeDelivery(): bool
    {
        return $this->type === 'free_delivery';
    }

    public function calculateDiscount(float $subTotal): float
    {
        if ($this->grantsFreeDelivery()) {
            return 0.0;
        }

        if ($this->type === 'percentage') {
            return round(($subTotal * (float) $this->discount_value) / 100, 2);
        }

        return round(min((float) $this->discount_value, $subTotal), 2);
    }

    public function isUsableByUser(User $user): bool
    {
        return $this->usabilityErrorForUser($user) === null;
    }

    public function usabilityErrorForUser(User $user): ?string
    {
        if ($this->usage_limit && $this->totalOrdersUsed() >= $this->usage_limit) {
            return __('messages.Coupon total usage limit reached.');
        }

        if ($this->first_order_only && $this->userOrderCount($user) > 0) {
            return __('messages.Coupon is only valid on your first order.');
        }

        if ($this->usage_limit_per_user && $this->usage_limit_per_user <= $this->ordersUsedCountByUser($user)) {
            return __('messages.Coupon usage limit reached.');
        }

        return null;
    }

    public function isUsable(User $user): bool
    {
        return $this->isValid() && $this->isUsableByUser($user);
    }

    public function isExpired(): bool
    {
        return $this->validityStatus() === 'expired';
    }
}
