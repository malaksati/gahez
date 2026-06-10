<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_unit_id',
        'unit_name',
        'unit_name_ar',
        'unit_factor',
        'product_name',
        'product_name_ar',
        'product_slug',
        'product_sku',
        'variant_name',
        'variant_name_ar',
        'variant_sku',
        'quantity',
        'unit_price',
        'line_discount',
        'is_gift',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_discount' => 'decimal:2',
        'is_gift' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (OrderItem $item): void {
            $item->fillMissingSnapshotFields();
        });
    }

    public function fillMissingSnapshotFields(): void
    {
        $product = $this->product_id
            ? Product::withTrashed()->find($this->product_id)
            : null;
        $variant = $this->variant_id
            ? ProductVariant::withTrashed()->find($this->variant_id)
            : null;

        if ($product) {
            $productNameEn = $product->getTranslation('name', 'en', false);
            $productNameAr = $product->getTranslation('name', 'ar', false);

            $this->product_name = $this->product_name ?: ($productNameEn ?: ($productNameAr ?: $product->sku));
            $this->product_name_ar = $this->product_name_ar ?: ($productNameAr ?: null);
            $this->product_slug = $this->product_slug ?: ((string) $product->slug);
            $this->product_sku = $this->product_sku ?: ((string) $product->sku);
        }

        if ($variant) {
            $variantNameEn = $variant->getTranslation('name', 'en', false);
            $variantNameAr = $variant->getTranslation('name', 'ar', false);

            $this->variant_name = $this->variant_name ?: ($variantNameEn ?: ($variantNameAr ?: null));
            $this->variant_name_ar = $this->variant_name_ar ?: ($variantNameAr ?: null);
            $this->variant_sku = $this->variant_sku ?: ($variant->sku ?: null);
        }

        if ($this->product_unit_id) {
            $productUnit = ProductUnit::with('unit')->find($this->product_unit_id);

            if ($productUnit) {
                $unitNameEn = $productUnit->displayUnitName('en');
                $unitNameAr = $productUnit->displayUnitName('ar');

                $this->unit_name = $this->unit_name ?: ($unitNameEn ?: $unitNameAr);
                $this->unit_name_ar = $this->unit_name_ar ?: ($unitNameAr ?: $unitNameEn);
                $this->unit_factor = $this->unit_factor ?: max(1, (int) $productUnit->factor);
            }
        }
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }
}
