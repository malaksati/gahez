<?php

namespace App\Models;

use App\Models\Concerns\HasOptionalStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    use HasOptionalStock;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'unit_id',
        'sku',
        'price',
        'stock',
        'is_in_stock',
        'factor',
        'discount',
        'discount_type',
        'is_default',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'stock' => 'integer',
        'is_in_stock' => 'boolean',
        'factor' => 'integer',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function variantLabel(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $variant = $this->relationLoaded('productVariant')
            ? $this->productVariant
            : $this->productVariant()->with('values.variantOption.variant')->first();

        if (! $variant) {
            return null;
        }

        if ($variant->relationLoaded('values')) {
            $parts = $variant->values
                ->sortBy(fn ($value) => $value->variantOption?->variant_id ?? 0)
                ->map(fn ($value) => $value->getTranslation('value', $locale, false)
                    ?: $value->getTranslation('value', 'en', false))
                ->filter()
                ->values();

            if ($parts->isNotEmpty()) {
                return $parts->implode(' / ');
            }
        }

        return $variant->getTranslation('name', $locale, false)
            ?: $variant->getTranslation('name', 'en', false)
            ?: $variant->sku;
    }

    public function getFinalPriceAttribute(): float
    {
        if (! $this->discount || $this->discount <= 0) {
            return (float) $this->price;
        }

        if ($this->discount_type === 'percentage') {
            return (float) $this->price - (($this->price * $this->discount) / 100);
        }

        return (float) max(0, $this->price - $this->discount);
    }

    public function displayUnitName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->relationLoaded('unit') && $this->unit) {
            return $this->unit->getTranslation('name', $locale, false)
                ?: $this->unit->getTranslation('name', 'en', false);
        }

        return null;
    }

    public function formattedLabel(?string $locale = null): ?string
    {
        $name = $this->displayUnitName($locale);

        return $name !== '' ? $name : null;
    }
}
