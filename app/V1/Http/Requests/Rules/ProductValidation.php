<?php

namespace App\V1\Http\Requests\Rules;

use App\V1\DataTransfer\Support\ImportRelationResolver;
use Illuminate\Validation\Rule;

final class ProductValidation
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeOptionalFields(array $input): array
    {
        foreach (['sort_order', 'stock'] as $field) {
            if (array_key_exists($field, $input) && $input[$field] === '') {
                $input[$field] = null;
            }
        }

        return $input;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return array_merge(TranslatableRules::field('name'), TranslatableRules::field('description', max: 5000), [
            'type' => ['required', Rule::in(['simple', 'variable'])],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_in_stock' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_new' => ['sometimes', 'boolean'],
            'is_approved' => ['sometimes', 'boolean'],
            'is_bookable' => ['sometimes', 'boolean'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ]);
    }

    /**
     * Rules for variable product SKUs and selected catalog variant options.
     *
     * @return array<string, list<mixed>>
     */
    public static function adminProductVariants(): array
    {
        return [
            'product_variants' => ['nullable', 'array'],
            'product_variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_variants.*.sku' => ['required_with:product_variants', 'string', 'max:100', 'distinct'],
            'product_variants.*.slug' => ['nullable', 'string', 'max:255'],
            'product_variants.*.name' => ['nullable', 'array'],
            'product_variants.*.name.en' => ['nullable', 'string', 'max:255'],
            'product_variants.*.name.ar' => ['nullable', 'string', 'max:255'],
            'product_variants.*.name_en' => ['nullable', 'string', 'max:255'],
            'product_variants.*.name_ar' => ['nullable', 'string', 'max:255'],
            'product_variants.*.price' => ['required_with:product_variants', 'numeric', 'min:0'],
            'product_variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'product_variants.*.is_in_stock' => ['sometimes', 'boolean'],
            'product_variants.*.discount' => ['nullable', 'numeric', 'min:0'],
            'product_variants.*.discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'product_variants.*.is_active' => ['sometimes', 'boolean'],
            'product_variants.*.option_ids' => ['required_with:product_variants', 'array', 'min:1'],
            'product_variants.*.option_ids.*' => ['integer', 'exists:variant_options,id'],
            'product_variants.*.thumbnail' => ['nullable', 'image', 'max:5120'],
        ];
    }

    /**
     * Extra rules for admin create/update (uploads & related products).
     *
     * @return array<string, list<mixed>>
     */
    public static function adminMediaAndRelations(?int $excludeProductId = null): array
    {
        $relatedProductRules = ['integer', 'exists:products,id', 'distinct'];

        if ($excludeProductId) {
            $relatedProductRules[] = Rule::notIn([$excludeProductId]);
        }

        return [
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
            'existing_images' => ['nullable', 'array'],
            'existing_images.*' => ['integer', 'exists:product_images,id'],
            'related_products' => ['nullable', 'array'],
            'related_products.*' => $relatedProductRules,
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function update(?int $productId = null): array
    {
        return array_merge(
            TranslatableRules::field('name', required: false),
            TranslatableRules::field('description', required: false, max: 5000),
            [
                'type' => ['sometimes', Rule::in(['simple', 'variable'])],
                'sku' => ['sometimes', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
                'price' => ['sometimes', 'numeric', 'min:0'],
                'stock' => ['nullable', 'integer', 'min:0'],
                'is_in_stock' => ['sometimes', 'boolean'],
                'sort_order' => ['sometimes', 'nullable', 'integer', 'min:1'],
                'discount' => ['nullable', 'numeric', 'min:0'],
                'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
                'is_active' => ['sometimes', 'boolean'],
                'is_featured' => ['sometimes', 'boolean'],
                'is_new' => ['sometimes', 'boolean'],
                'is_approved' => ['sometimes', 'boolean'],
                'is_bookable' => ['sometimes', 'boolean'],
                'brand_id' => ['sometimes', 'integer', 'exists:brands,id'],
                'category_ids' => ['sometimes', 'array'],
                'category_ids.*' => ['integer', 'exists:categories,id'],
            ]
        );
    }

    /**
     * Spreadsheet import: validates the full mapped product payload before upsert.
     *
     * @param  array<string, mixed>  $product
     * @param  list<int>  $categoryIds
     * @return array<string, list<mixed>>
     */
    public static function importRow(array $product, array $categoryIds, ?int $existingProductId = null): array
    {
        $rules = $existingProductId
            ? self::update($existingProductId)
            : self::store();

        if ($categoryIds === []) {
            unset($rules['category_ids'], $rules['category_ids.*']);
        }

        if ($product['brand_id'] === null) {
            $rules['brand_id'] = ImportRelationResolver::shouldSkipMissingRelations()
                ? ['nullable']
                : ['required', 'integer', 'exists:brands,id'];
        }

        if ($product['discount'] === null) {
            $rules['discount'] = ['nullable'];
        }

        if ($product['discount_type'] === null) {
            unset($rules['discount_type']);
        } else {
            $rules['discount_type'] = ['nullable', Rule::in(['percentage', 'fixed'])];
        }

        $description = $product['description'] ?? [];

        if (
            trim((string) ($description['en'] ?? '')) === ''
            && trim((string) ($description['ar'] ?? '')) === ''
        ) {
            foreach (array_keys($rules) as $key) {
                if (str_starts_with($key, 'description')) {
                    unset($rules[$key]);
                }
            }

            $rules = array_merge($rules, TranslatableRules::field('description', required: false, max: 5000));
        }

        return $rules;
    }
}
