<?php

namespace App\Models;

use App\Models\Concerns\HasOptionalStock;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory, HasOptionalStock, SoftDeletes, HasTranslations;

    protected $fillable = [
        'name',
        'product_id',
        'slug',
        'sku',
        'price',
        'stock',
        'is_in_stock',
        'discount',
        'discount_type',
        'is_active',
        'thumbnail',
    ];

    protected $casts = [
        'name' => 'array',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'discount' => 'decimal:2',
        'discount_type' => 'string',
        'is_active' => 'boolean',
        'is_in_stock' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variation) {
            // Check if product type is variable (not simple)
            if ($variation->product->type !== 'variable') {
                throw new \Exception("Cannot add variations to a simple product.");
            }
        });
    }

    public $translatable = ['name'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    /**
     * Scope a query to only include active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if variant is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/product_image.jpg')
        );
    }
}
