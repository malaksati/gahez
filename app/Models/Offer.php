<?php

namespace App\Models;

use App\Models\Concerns\HasValidityPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Offer extends Model
{
    use HasFactory, HasTranslations, HasValidityPeriod;

    protected $fillable = [
        'name',
        'type',
        'value',
        'bogo_buy_quantity',
        'bogo_bonus_quantity',
        'bogo_bonus_discount_type',
        'bogo_bonus_discount_value',
        'min_cart_amount',
        'max_discounted_quantity',
        'ends_when_out_of_stock',
        'start_date',
        'end_date',
        'is_active',
        'show_countdown',
        'offerable_id',
        'offerable_type',
    ];

    protected $casts = [
        'name' => 'array',
        'value' => 'decimal:2',
        'bogo_buy_quantity' => 'integer',
        'bogo_bonus_quantity' => 'integer',
        'bogo_bonus_discount_value' => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
        'max_discounted_quantity' => 'integer',
        'ends_when_out_of_stock' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'show_countdown' => 'boolean',
    ];

    public function shouldShowCountdown(): bool
    {
        return $this->show_countdown && $this->end_date !== null;
    }

    protected $translatable = ['name'];

    // Polymorphic relationship to the offerable model (Product, Category, etc.)
    public function offerable()
    {
        return $this->morphTo();
    }

    public function rewardProducts()
    {
        return $this->hasMany(OfferRewardProduct::class)->orderBy('sort_order');
    }

    public function isCartThresholdType(): bool
    {
        return in_array($this->type, ['threshold_gift', 'free_delivery'], true);
    }

    public function isProductDiscountType(): bool
    {
        return in_array($this->type, ['fixed', 'percentage', 'bogo'], true);
    }

    public function isBogoOffer(): bool
    {
        return $this->type === 'bogo';
    }

    public function isProductScopedBogo(): bool
    {
        return $this->isBogoOffer() && $this->offerable_type === Product::class;
    }

    public function isCategoryScopedBogo(): bool
    {
        return $this->isBogoOffer() && $this->offerable_type === Category::class;
    }

    public function bogoAutoAddsBonusQuantity(): bool
    {
        if (! $this->isBogoOffer()) {
            return false;
        }

        return $this->bogo_bonus_discount_type === 'percentage'
            && (float) $this->bogo_bonus_discount_value >= 100;
    }

    public function scopeByOfferableType($query, string $offerableType)
    {
        return $query->where('offerable_type', $offerableType);
    }

    public function scopeByOfferableId($query, $offerableId)
    {
        return $query->where('offerable_id', $offerableId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
