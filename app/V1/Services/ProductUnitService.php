<?php

namespace App\V1\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductUnitService
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function syncForProduct(Product $product, array $rows): void
    {
        $rows = collect($rows)
            ->filter(fn ($row) => isset($row['unit_id']) && (int) $row['unit_id'] > 0)
            ->map(function ($row) use ($product) {
                $variantId = $this->resolveProductVariantId($product, $row);

                return array_merge($row, [
                    'resolved_product_variant_id' => $variantId,
                ]);
            })
            ->unique(fn ($row) => (int) $row['unit_id'].'-'.($row['resolved_product_variant_id'] ?? 'null'))
            ->values();

        if ($rows->isEmpty()) {
            $this->ensureDefaultFromProduct($product);

            return;
        }

        $defaultIndex = $rows->search(fn ($row) => filter_var($row['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN));

        if ($defaultIndex === false) {
            $defaultIndex = 0;
        }

        $keepIds = [];

        DB::transaction(function () use ($product, $rows, $defaultIndex, &$keepIds) {
            foreach ($rows as $index => $row) {
                $unitId = (int) $row['unit_id'];
                $variantId = $row['resolved_product_variant_id'] ?? null;

                $payload = [
                    'unit_id' => $unitId,
                    'product_variant_id' => $variantId,
                    'sku' => $this->resolveSku($product, $unitId, $row['sku'] ?? null, $variantId),
                    'price' => (float) ($row['price'] ?? 0),
                    'stock' => $this->nullableInt($row['stock'] ?? null),
                    'is_in_stock' => filter_var($row['is_in_stock'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'factor' => max(1, (int) ($row['factor'] ?? 1)),
                    'discount' => isset($row['discount']) && $row['discount'] !== '' ? (float) $row['discount'] : null,
                    'discount_type' => $this->nullableString($row['discount_type'] ?? null),
                    'is_default' => $index === $defaultIndex,
                    'sort_order' => (int) ($row['sort_order'] ?? $index),
                    'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ];

                $productUnit = null;

                if (! empty($row['id'])) {
                    $productUnit = ProductUnit::query()
                        ->where('product_id', $product->id)
                        ->whereKey((int) $row['id'])
                        ->first();
                }

                if (! $productUnit) {
                    $productUnit = ProductUnit::query()
                        ->where('product_id', $product->id)
                        ->where('unit_id', $unitId)
                        ->when(
                            $variantId,
                            fn ($query) => $query->where('product_variant_id', $variantId),
                            fn ($query) => $query->whereNull('product_variant_id'),
                        )
                        ->first();
                }

                if ($productUnit) {
                    $productUnit->update($payload);
                    $keepIds[] = $productUnit->id;

                    continue;
                }

                $created = ProductUnit::query()->create(array_merge($payload, [
                    'product_id' => $product->id,
                ]));
                $keepIds[] = $created->id;
            }

            ProductUnit::query()
                ->where('product_id', $product->id)
                ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))
                ->delete();
        });
    }

    protected function ensureDefaultFromProduct(Product $product): void
    {
        if (ProductUnit::query()->where('product_id', $product->id)->exists()) {
            return;
        }

        if ($product->isVariable()) {
            return;
        }

        $pieceUnitId = Unit::query()->where('code', 'piece')->value('id');

        if (! $pieceUnitId) {
            return;
        }

        ProductUnit::query()->create([
            'product_id' => $product->id,
            'unit_id' => $pieceUnitId,
            'sku' => $this->generateSkuForUnit($product, $pieceUnitId),
            'price' => 0,
            'stock' => null,
            'is_in_stock' => $product->is_in_stock,
            'factor' => 1,
            'discount' => $product->discount,
            'discount_type' => $product->discount_type,
            'is_default' => true,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serializeForWizard(Product $product): array
    {
        return $product->productUnits()
            ->with(['unit', 'productVariant.values'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (ProductUnit $row) {
                $optionIds = [];

                if ($row->product_variant_id && $row->relationLoaded('productVariant') && $row->productVariant) {
                    $optionIds = $row->productVariant->values
                        ->pluck('variant_option_id')
                        ->map(fn ($id) => (string) $id)
                        ->values()
                        ->all();
                }

                return [
                    'id' => $row->id,
                    'product_variant_id' => $row->product_variant_id ? (string) $row->product_variant_id : '',
                    'variant_option_ids' => $optionIds,
                    'variant_key' => $this->comboKey($optionIds),
                    'unit_id' => (string) $row->unit_id,
                    'sku' => $row->sku ?? '',
                    'price' => (string) $row->price,
                    'stock' => $row->stock !== null ? (string) $row->stock : '',
                    'is_in_stock' => $row->is_in_stock,
                    'factor' => (string) $row->factor,
                    'discount' => $row->discount !== null ? (string) $row->discount : '0',
                    'discount_type' => $row->discount_type ?? 'percentage',
                    'is_default' => $row->is_default,
                    'is_active' => $row->is_active,
                    'sort_order' => (string) $row->sort_order,
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function resolveProductVariantId(Product $product, array $row): ?int
    {
        if (! empty($row['product_variant_id'])) {
            $variantId = (int) $row['product_variant_id'];

            return ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey($variantId)
                ->exists()
                ? $variantId
                : null;
        }

        $optionIds = collect($row['variant_option_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->sort()
            ->values();

        if ($optionIds->isEmpty()) {
            return $product->isVariable() ? null : null;
        }

        $variants = $product->relationLoaded('variants')
            ? $product->variants
            : $product->variants()->with('values')->get();

        foreach ($variants as $variant) {
            $variantOptionIds = $variant->values
                ->pluck('variant_option_id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();

            if ($variantOptionIds->toArray() === $optionIds->toArray()) {
                return $variant->id;
            }
        }

        return null;
    }

  /**
     * @param  list<string|int>  $optionIds
     */
    protected function comboKey(array $optionIds): string
    {
        return collect($optionIds)
            ->map(fn ($id) => (string) $id)
            ->sort()
            ->implode('-');
    }

    private function resolveSku(Product $product, int $unitId, mixed $providedSku, ?int $variantId = null): ?string
    {
        $sku = trim((string) $providedSku);

        if ($sku !== '') {
            return $sku;
        }

        return $this->generateSkuForUnit($product, $unitId, $variantId);
    }

    private function generateSkuForUnit(Product $product, int $unitId, ?int $variantId = null): ?string
    {
        $productSku = trim((string) $product->sku);

        if ($productSku === '') {
            return null;
        }

        $unitCode = trim(strtolower((string) Unit::query()->whereKey($unitId)->value('code')));

        $parts = [$productSku];

        if ($variantId) {
            $variant = ProductVariant::query()->whereKey($variantId)->value('sku');
            if ($variant) {
                $parts[] = trim((string) $variant);
            }
        }

        if ($unitCode !== '') {
            $parts[] = $unitCode;
        }

        return implode('-', array_filter($parts));
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
