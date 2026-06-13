<?php

namespace App\V1\Http\Requests\Rules;

use App\Models\Product;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

final class ProductValidation
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalizeOptionalFields(array $input): array
    {
        return $input;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function hasSellableUnits(array $input): bool
    {
        foreach ($input['product_units'] ?? [] as $row) {
            if (isset($row['unit_id']) && (int) $row['unit_id'] > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return array_merge(
            TranslatableRules::field('name'),
            TranslatableRules::field('description', max: 5000),
            self::adminProductUnits(),
            [
                'type' => ['required', Rule::in(['simple', 'variable'])],
                'sku' => self::productSkuRules(),
                'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
                'is_in_stock' => ['sometimes', 'boolean'],
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
            ],
        );
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
            'product_variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'product_variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'product_variants.*.is_in_stock' => ['sometimes', 'boolean'],
            'product_variants.*.discount' => ['nullable', 'numeric', 'min:0'],
            'product_variants.*.discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'product_variants.*.is_active' => ['sometimes', 'boolean'],
            'product_variants.*.option_ids' => ['required_with:product_variants', 'array', 'min:1'],
            'product_variants.*.option_ids.*' => ['integer', 'exists:variant_options,id'],
            'product_variants.*.thumbnail' => self::adminImageUploadRule(),
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
            'thumbnail' => self::adminImageUploadRule(),
            'images' => ['nullable', 'array'],
            'images.*' => self::adminImageUploadRule(),
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
            self::adminProductUnits(),
            [
                'type' => ['sometimes', Rule::in(['simple', 'variable'])],
                'sku' => self::productSkuRules($productId, required: false),
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
                'is_in_stock' => ['sometimes', 'boolean'],
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
     * @return array<string, list<mixed>>
     */
    public static function adminProductUnits(): array
    {
        return [
            'product_units' => ['nullable', 'array'],
            'product_units.*.id' => ['nullable', 'integer', 'exists:product_units,id'],
            'product_units.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_units.*.variant_option_ids' => ['nullable', 'array'],
            'product_units.*.variant_option_ids.*' => ['integer', 'exists:variant_options,id'],
            'product_units.*.unit_id' => ['required_with:product_units', 'integer', 'exists:units,id'],
            'product_units.*.sku' => ['nullable', 'string', 'max:100'],
            'product_units.*.price' => ['required_with:product_units', 'numeric', 'min:0'],
            'product_units.*.stock' => ['nullable', 'integer', 'min:0'],
            'product_units.*.is_in_stock' => ['sometimes', 'boolean'],
            'product_units.*.factor' => ['nullable', 'integer', 'min:1'],
            'product_units.*.discount' => ['nullable', 'numeric', 'min:0'],
            'product_units.*.discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'product_units.*.is_default' => ['sometimes', 'boolean'],
            'product_units.*.is_active' => ['sometimes', 'boolean'],
            'product_units.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
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

    /**
     * @return list<mixed>
     */
    public static function adminImageUploadRule(): array
    {
        return [
            'nullable',
            'file',
            'max:5120',
            static function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $value instanceof UploadedFile) {
                    return;
                }

                if (! $value->isValid()) {
                    $fail(__('validation.uploaded', ['attribute' => $attribute]));

                    return;
                }

                if (ProductValidation::isAllowedAdminImageUpload($value)) {
                    return;
                }

                $fail(__('The :attribute must be a file of type: jpg, jpeg, png, gif, webp, bmp.'));
            },
        ];
    }

    public static function isAllowedAdminImageUpload(UploadedFile $file): bool
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

        if (in_array(strtolower($file->getClientOriginalExtension()), $allowedExtensions, true)) {
            return true;
        }

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-ms-bmp',
            'application/octet-stream',
        ];

        $mime = strtolower((string) $file->getMimeType());

        if (in_array($mime, $allowedMimes, true) && @getimagesize($file->getRealPath()) !== false) {
            return true;
        }

        $clientMime = strtolower((string) $file->getClientMimeType());

        if (in_array($clientMime, $allowedMimes, true) && @getimagesize($file->getRealPath()) !== false) {
            return true;
        }

        return @getimagesize($file->getRealPath()) !== false;
    }

    /**
     * @return list<mixed>
     */
    public static function productSkuRules(?int $ignoreProductId = null, bool $required = true): array
    {
        $rules = [
            'string',
            'max:100',
            static function (string $attribute, mixed $value, \Closure $fail) use ($ignoreProductId): void {
                if (Product::isSkuTaken(trim((string) $value), $ignoreProductId)) {
                    $fail(__('messages.This SKU is already in use.'));
                }
            },
        ];

        return $required
            ? array_merge(['required'], $rules)
            : array_merge(['sometimes'], $rules);
    }
}
