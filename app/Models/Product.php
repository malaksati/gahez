<?php

namespace App\Models;

use App\Models\Concerns\HasOptionalStock;
use App\Factories\ProductPriceStockFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory, HasOptionalStock, HasTranslations, Sluggable, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'description',
        'thumbnail',
        'sku',
        'slug',
        'price',
        'stock',
        'is_in_stock',
        'sort_order',
        'discount',
        'discount_type',
        'is_active',
        'is_featured',
        'is_new',
        'is_approved',
        'is_bookable',
        'is_visible',
        'brand_id',
        'brand_snapshot',
        'category_snapshot',
    ];

    protected $translatable = ['name', 'description'];

    /**
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name.en',
            ],
        ];
    }

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'stock' => 'integer',
        'is_in_stock' => 'boolean',
        'sort_order' => 'integer',
        'discount_type' => 'string',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_approved' => 'boolean',
        'is_bookable' => 'boolean',
        'brand_snapshot' => 'array',
        'category_snapshot' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            $product->syncBrandSnapshot();
            $product->syncCategorySnapshot();
        });
    }

    /**
     * Scope a query to only include active products
     */
    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products
     */
    public function scopeFeatured(Builder $builder)
    {
        return $builder->where('is_featured', true);
    }

    /**
     * Scope a query to only include approved products
     */
    public function scopeApproved(Builder $builder)
    {
        return $builder->where('is_approved', true);
    }

    /**
     * Scope a query to only include new products
     */
    public function scopeNew(Builder $builder)
    {
        return $builder->where('is_new', true);
    }

    /**
     * Scope a query to only include bookable products
     */
    public function scopeBookable(Builder $builder)
    {
        return $builder->where('is_bookable', true);
    }

    /**
     * Scope a query to only include simple products
     */
    public function scopeSimple(Builder $builder)
    {
        return $builder->where('type', 'simple');
    }

    /**
     * Scope a query to only include variable products
     */
    public function scopeVariable(Builder $builder)
    {
        return $builder->where('type', 'variable');
    }

    /**
     * Calculate the final price after discount
     */
    public function getFinalPriceAttribute(): float
    {
        if (! $this->discount || $this->discount <= 0) {
            return (float) $this->price;
        }

        if ($this->discount_type == 'percentage') {
            return (float) $this->price - (($this->price * $this->discount) / 100);
        }

        // Fixed discount
        return (float) max(0, $this->price - $this->discount);
    }

    /**
     * Check if product has discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount > 0;
    }

    /**
     * Check if product is variable type
     */
    public function isVariable(): bool
    {
        return $this->type == 'variable';
    }

    /**
     * Check if product is simple type
     */
    public function isSimple(): bool
    {
        return $this->type == 'simple';
    }

    public function isPurchasable(): bool
    {
        return $this->is_active && $this->is_approved && $this->is_bookable;
    }

    /**
     * Get the main image (first image or thumbnail)
     */
    public function getMainImageAttribute()
    {
        $firstImage = $this->images()->first();

        return $firstImage ? $firstImage->image_path : $this->thumbnail;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function syncBrandSnapshot(): void
    {
        if (! $this->brand_id) {
            return;
        }

        $brand = $this->relationLoaded('brand') ? $this->brand : Brand::query()->find($this->brand_id);

        if (! $brand) {
            return;
        }

        $nameEn = $brand->getTranslation('name', 'en', false);
        $nameAr = $brand->getTranslation('name', 'ar', false);

        $this->brand_snapshot = [
            'id' => $brand->id,
            'name' => $nameEn ?: ($nameAr ?: null),
            'name_ar' => $nameAr ?: null,
        ];
    }

    public function displayBrandName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->brand) {
            return $this->brand->getTranslation('name', $locale, false)
                ?: $this->brand->getTranslation('name', 'en', false);
        }

        if (is_array($this->brand_snapshot) && $this->brand_snapshot !== []) {
            return $locale === 'ar'
                ? (($this->brand_snapshot['name_ar'] ?? null) ?: ($this->brand_snapshot['name'] ?? null))
                : (($this->brand_snapshot['name'] ?? null) ?: ($this->brand_snapshot['name_ar'] ?? null));
        }

        return null;
    }

    public function syncCategorySnapshot(): void
    {
        $categories = $this->relationLoaded('categories')
            ? $this->categories
            : $this->categories()->get();

        $this->category_snapshot = $categories
            ->map(function ($category) {
                $nameEn = $category->getTranslation('name', 'en', false);
                $nameAr = $category->getTranslation('name', 'ar', false);

                return [
                    'id' => $category->id,
                    'name' => $nameEn ?: ($nameAr ?: null),
                    'name_ar' => $nameAr ?: null,
                ];
            })
            ->values()
            ->all();
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function images()
    {
        return $this->morphMany(ProductImage::class, 'imageable');
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, 'offerable');
    }

    public function relatedProducts()
    {
        return $this->hasMany(ProductRelation::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantValues()
    {
        return $this->hasManyThrough(ProductVariantValue::class, ProductVariant::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function reports()
    {
        return $this->hasMany(ProductReport::class);
    }

    // public function manager()
    // {
    //     return ProductPriceStockFactory::make($this);
    // }

    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/product_image.jpg')
        );
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }
}
