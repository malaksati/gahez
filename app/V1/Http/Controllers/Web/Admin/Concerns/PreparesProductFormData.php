<?php

namespace App\V1\Http\Controllers\Web\Admin\Concerns;

use App\Models\Product;
use App\Models\Unit;
use App\V1\Services\ProductService;
use App\V1\Services\VariantService;
use Illuminate\Support\Collection;

trait PreparesProductFormData
{
    protected function productFormViewData(?Product $product, ProductService $products, VariantService $variants): array
    {
        $locale = app()->getLocale();

        return [
            'catalogVariants' => $this->serializeCatalogVariants($variants, $locale),
            'existingProductVariants' => $product
                ? $products->serializeProductVariantsForWizard($product)
                : [],
            'catalogUnits' => $this->catalogUnitsForProductForm($product),
            'existingProductUnits' => $product
                ? $products->serializeProductUnitsForWizard($product)
                : [],
        ];
    }

    protected function catalogUnitsForProductForm(?Product $product): Collection
    {
        $units = Unit::query()->active()->orderBy('code')->get();

        if ($product === null) {
            return $units;
        }

        $usedUnitIds = $product->productUnits()->pluck('unit_id')->unique();

        if ($usedUnitIds->isEmpty()) {
            return $units;
        }

        $missing = Unit::query()
            ->whereIn('id', $usedUnitIds)
            ->whereNotIn('id', $units->pluck('id'))
            ->orderBy('code')
            ->get();

        return $units->merge($missing)->unique('id')->sortBy('code')->values();
    }

    protected function serializeCatalogVariants(VariantService $variants, string $locale): Collection
    {
        return $variants->getActiveVariants()
            ->load('options')
            ->map(fn ($variant) => $this->serializeCatalogVariant($variant, $locale))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeCatalogVariant(\App\Models\Variant $variant, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return [
            'id' => $variant->id,
            'name' => $variant->getTranslation('name', $locale, false)
                ?: $variant->getTranslation('name', 'en'),
            'is_required' => $variant->is_required,
            'options' => $variant->options->map(fn ($option) => $this->serializeCatalogOption($option, $locale))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeCatalogUnit(Unit $unit, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return [
            'id' => $unit->id,
            'code' => $unit->code,
            'name' => $unit->getTranslation('name', $locale, false)
                ?: $unit->getTranslation('name', 'en'),
            'name_en' => $unit->getTranslation('name', 'en', false)
                ?: $unit->getTranslation('name', 'en'),
            'name_ar' => $unit->getTranslation('name', 'ar', false)
                ?: $unit->getTranslation('name', 'ar'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeCatalogOption(\App\Models\VariantOption $option, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return [
            'id' => $option->id,
            'name' => $option->getTranslation('name', $locale, false)
                ?: $option->getTranslation('name', 'en'),
            'name_en' => $option->getTranslation('name', 'en', false)
                ?: $option->getTranslation('name', 'en'),
            'name_ar' => $option->getTranslation('name', 'ar', false)
                ?: $option->getTranslation('name', 'ar'),
            'code' => $option->code,
        ];
    }
}
