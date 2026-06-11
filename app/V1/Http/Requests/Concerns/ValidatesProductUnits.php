<?php

namespace App\V1\Http\Requests\Concerns;

use App\Models\Product;
use App\V1\Http\Requests\Rules\ProductValidation;
use Illuminate\Validation\Validator;

trait ValidatesProductUnits
{
    protected function prepareProductUnitsInput(): void
    {
        $rows = collect($this->input('product_units', []))
            ->filter(fn ($row) => is_array($row) && (int) ($row['unit_id'] ?? 0) > 0)
            ->values()
            ->all();

        $this->merge(['product_units' => $rows]);
    }

    protected function validateProductUnits(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rows = collect($this->input('product_units', []))
                ->filter(fn ($row) => isset($row['unit_id']) && (int) $row['unit_id'] > 0);

            if ($rows->isEmpty()) {
                $validator->errors()->add(
                    'product_units',
                    __('messages.Product units required'),
                );

                return;
            }

            if ($this->input('type') === 'variable') {
                foreach ($rows as $index => $row) {
                    $optionIds = collect($row['variant_option_ids'] ?? [])
                        ->filter(fn ($id) => (int) $id > 0);

                    if ($optionIds->isEmpty() && empty($row['product_variant_id'])) {
                        $validator->errors()->add(
                            "product_units.{$index}.variant_option_ids",
                            __('messages.Product unit variant required'),
                        );
                    }
                }
            }

            $compositeKeys = $rows->map(function ($row) {
                $variantKey = ! empty($row['product_variant_id'])
                    ? (int) $row['product_variant_id']
                    : collect($row['variant_option_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->sort()
                        ->implode('-');

                return (int) $row['unit_id'].'|'.$variantKey;
            });

            if ($compositeKeys->duplicates()->filter()->isNotEmpty()) {
                $validator->errors()->add(
                    'product_units',
                    __('messages.Product units must use unique units'),
                );
            }
        });
    }

    protected function prepareProductPricingFields(): void
    {
        $this->merge(ProductValidation::normalizeOptionalFields($this->all()));
    }

    protected function prepareProductSlugField(?int $ignoreProductId = null): void
    {
        $slug = $this->input('slug');

        if ($slug === null || trim((string) $slug) === '') {
            return;
        }

        $this->merge([
            'slug' => Product::ensureUniqueSlug((string) $slug, $ignoreProductId),
        ]);
    }
}
