<?php

namespace App\V1\Http\Controllers\Web\Admin\Concerns;

use App\Models\Product;
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
        ];
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
