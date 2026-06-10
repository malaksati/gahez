<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Unit extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $translatable = ['name'];

    protected $casts = [
        'name' => 'array',
        'is_active' => 'boolean',
    ];

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
