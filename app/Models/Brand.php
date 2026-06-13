<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Brand extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'image',
    ];

    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value
                ? asset('storage/'.$value)
                : asset('dashboard/images/category_image.png')
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
