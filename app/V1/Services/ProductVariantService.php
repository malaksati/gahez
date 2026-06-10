<?php

namespace App\V1\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOption;
use App\V1\Support\UploadStorage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductVariantService
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function syncForProduct(Product $product, array $rows, string $productType, ?Request $request = null): void
    {
        if ($productType !== 'variable') {
            $product->variants()->each(fn (ProductVariant $variant) => $variant->delete());

            return;
        }

        $keepIds = [];

        foreach ($rows as $index => $row) {
            $variant = $this->upsertProductVariant($product, $row);
            $keepIds[] = $variant->id;
            $thumbnailPath = $this->resolveCombinationThumbnailPath($variant, $request, $index);
            $this->syncVariantValues($variant, $row['option_ids'] ?? [], $thumbnailPath);
        }

        $product->variants()->whereNotIn('id', $keepIds)->delete();

        $this->syncParentStock($product);
    }

    public function syncParentStock(Product $product): void
    {
        if ($product->type !== 'variable') {
            return;
        }

        $trackedTotal = $product->variants()
            ->whereNotNull('stock')
            ->sum('stock');

        $hasUntracked = $product->variants()->whereNull('stock')->exists();

        $product->update([
            'stock' => $hasUntracked && $trackedTotal === 0 ? null : (int) $trackedTotal,
        ]);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function upsertProductVariant(Product $product, array $row): ProductVariant
    {
        $name = [
            'en' => trim((string) ($row['name']['en'] ?? $row['name_en'] ?? '')),
            'ar' => trim((string) ($row['name']['ar'] ?? $row['name_ar'] ?? '')),
        ];

        if ($name['en'] === '' && $name['ar'] === '') {
            $name = $this->buildNameFromOptions($row['option_ids'] ?? []);
        }

        $sku = trim((string) ($row['sku'] ?? ''));
        $slug = trim((string) ($row['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($sku !== '' ? $sku : ($name['en'] ?: 'variant-'.uniqid()));
        }

        $payload = [
            'product_id' => $product->id,
            'name' => $name,
            'sku' => $sku,
            'slug' => $slug,
            'price' => (float) ($row['price'] ?? 0),
            'stock' => $this->normalizeStockValue($row['stock'] ?? null),
            'is_in_stock' => filter_var($row['is_in_stock'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'discount' => (float) ($row['discount'] ?? 0),
            'discount_type' => $row['discount_type'] ?? 'percentage',
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ];

        if (! empty($row['id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->findOrFail((int) $row['id']);
            $variant->update($payload);

            return $variant;
        }

        $variant = ProductVariant::query()->make($payload);
        $variant->product_id = $product->id;
        $variant->fill($payload);
        $variant->save();

        return $variant;
    }

    protected function resolveCombinationThumbnailPath(
        ProductVariant $variant,
        ?Request $request = null,
        ?int $index = null,
    ): ?string {
        if ($request && $index !== null) {
            $thumbnail = $request->file("product_variants.{$index}.thumbnail");

            if ($thumbnail instanceof UploadedFile && $thumbnail->isValid()) {
                $existingPath = $this->existingCombinationThumbnail($variant);
                $newPath = UploadStorage::store($thumbnail, 'products/variants');

                if ($existingPath && $existingPath !== $newPath) {
                    $this->deleteStoredThumbnail($existingPath);
                }

                return $newPath;
            }
        }

        return $this->existingCombinationThumbnail($variant);
    }

    protected function existingCombinationThumbnail(ProductVariant $variant): ?string
    {
        $fromValue = $variant->values()->whereNotNull('thumbnail')->value('thumbnail');

        if ($fromValue) {
            return $fromValue;
        }

        return $variant->getRawOriginal('thumbnail');
    }

    protected function deleteStoredThumbnail(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @param  list<int|string>  $optionIds
     */
    protected function syncVariantValues(ProductVariant $variant, array $optionIds, ?string $thumbnailPath = null): void
    {
        $variant->values()->delete();

        $ids = collect($optionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        foreach ($ids as $optionId) {
            $option = VariantOption::query()->find($optionId);
            if (! $option) {
                continue;
            }

            $variant->values()->create([
                'variant_option_id' => $optionId,
                'value' => $option->getTranslations('name'),
                'thumbnail' => $thumbnailPath,
            ]);
        }
    }

    /**
     * @param  list<int|string>  $optionIds
     * @return array{en: string, ar: string}
     */
    protected function normalizeStockValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (int) $value);
    }

    protected function buildNameFromOptions(array $optionIds): array
    {
        $names = ['en' => [], 'ar' => []];

        foreach ($optionIds as $optionId) {
            $option = VariantOption::query()->find((int) $optionId);
            if (! $option) {
                continue;
            }

            $names['en'][] = $option->getTranslation('name', 'en', false) ?: $option->getTranslation('name', 'en');
            $names['ar'][] = $option->getTranslation('name', 'ar', false) ?: $option->getTranslation('name', 'ar');
        }

        return [
            'en' => implode(' / ', array_filter($names['en'])),
            'ar' => implode(' / ', array_filter($names['ar'])),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function serializeForWizard(Product $product): array
    {
        return $product->variants()
            ->with(['values.variantOption.variant'])
            ->get()
            ->map(function (ProductVariant $variant) {
                $optionIdsByVariant = [];
                foreach ($variant->values as $value) {
                    $variantId = $value->variantOption?->variant_id;
                    if ($variantId) {
                        $optionIdsByVariant[(string) $variantId] = (string) $value->variant_option_id;
                    }
                }

                $thumbnailPath = $variant->values
                    ->map(fn ($value) => $value->getRawOriginal('thumbnail'))
                    ->filter()
                    ->first()
                    ?? $variant->getRawOriginal('thumbnail');

                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'slug' => $variant->slug,
                    'name_en' => $variant->getTranslation('name', 'en', false) ?: '',
                    'name_ar' => $variant->getTranslation('name', 'ar', false) ?: '',
                    'price' => (string) $variant->price,
                    'stock' => $variant->stock !== null ? (string) $variant->stock : '',
                    'is_in_stock' => $variant->is_in_stock,
                    'discount' => (string) ($variant->discount ?? 0),
                    'discount_type' => $variant->discount_type ?? 'percentage',
                    'is_active' => $variant->is_active,
                    'thumbnail_preview' => $thumbnailPath ? asset('storage/'.$thumbnailPath) : null,
                    'option_ids' => $variant->values->pluck('variant_option_id')->map(fn ($id) => (string) $id)->values()->all(),
                    'options_by_variant' => $optionIdsByVariant,
                ];
            })
            ->values()
            ->all();
    }
}
