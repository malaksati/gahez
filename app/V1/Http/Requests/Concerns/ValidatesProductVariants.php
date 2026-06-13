<?php

namespace App\V1\Http\Requests\Concerns;

use App\Models\ProductVariant;
use App\Models\VariantOption;
use Illuminate\Validation\Validator;

trait ValidatesProductVariants
{
    protected function validateProductVariants(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('type') !== 'variable') {
                return;
            }

            $rows = $this->input('product_variants', []);

            if ($rows === [] || $rows === null) {
                $validator->errors()->add(
                    'product_variants',
                    __('messages.At least one product variant is required.'),
                );

                return;
            }

            $combinations = [];

            foreach ($rows as $index => $row) {
                $sku = trim((string) ($row['sku'] ?? ''));
                if ($sku === '') {
                    continue;
                }

                $skuQuery = ProductVariant::withTrashed()->where('sku', $sku);
                if (! empty($row['id'])) {
                    $skuQuery->where('id', '!=', (int) $row['id']);
                }
                if ($skuQuery->exists()) {
                    $validator->errors()->add(
                        "product_variants.{$index}.sku",
                        __('messages.This SKU is already in use.'),
                    );
                }

                $optionIds = collect($row['option_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn (int $id) => $id > 0)
                    ->sort()
                    ->values();

                if ($optionIds->isEmpty()) {
                    $validator->errors()->add(
                        "product_variants.{$index}.option_ids",
                        __('messages.Select an option for each variant attribute.'),
                    );

                    continue;
                }

                $options = VariantOption::query()
                    ->with('variant')
                    ->whereIn('id', $optionIds)
                    ->get();

                if ($options->count() !== $optionIds->count()) {
                    $validator->errors()->add(
                        "product_variants.{$index}.option_ids",
                        __('messages.Invalid variant option selected.'),
                    );

                    continue;
                }

                $variantIdsForRow = $options->pluck('variant_id')->unique();
                if ($variantIdsForRow->count() !== $optionIds->count()) {
                    $validator->errors()->add(
                        "product_variants.{$index}.option_ids",
                        __('messages.Only one option per variant attribute is allowed.'),
                    );
                }

                $comboKey = $optionIds->implode('-');
                if (isset($combinations[$comboKey])) {
                    $validator->errors()->add(
                        "product_variants.{$index}.option_ids",
                        __('messages.Duplicate variant combination.'),
                    );
                }
                $combinations[$comboKey] = true;
            }
        });
    }
}
