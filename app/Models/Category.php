<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations, Sluggable, SoftDeletes;

    protected $fillable = [
        'name',
        'image',
        'is_active',
        'is_featured',
        'parent_id',
        'sort_order',
    ];

    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name.en',
            ],
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/category_image.png')
        );
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    public function offers()
    {
        return $this->morphMany(Offer::class, 'offerable');
    }
}
