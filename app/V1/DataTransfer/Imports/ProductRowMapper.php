<?php

namespace App\V1\DataTransfer\Imports;

use App\Models\Product;
use App\V1\DataTransfer\Support\ImportBooleanParser;
use App\V1\DataTransfer\Support\ImportDisplayNameNormalizer;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use App\V1\DataTransfer\Support\ImportSlugNormalizer;
use App\V1\DataTransfer\Support\ProductSpreadsheetColumns;
use Illuminate\Support\Str;

final class ProductRowMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array{
     *     product: array<string, mixed>,
     *     category_ids: list<int>,
     *     sync_categories: bool
     * }
     */
    public function toPayload(array $row): array
    {
        $row = ProductSpreadsheetColumns::canonicalizeRow($row);

        $nameEn = ImportDisplayNameNormalizer::name((string) ($row['name_en'] ?? ''));
        $nameAr = ImportDisplayNameNormalizer::name((string) ($row['name_ar'] ?? $nameEn), 'ar');
        if ($nameAr === '' && $nameEn !== '') {
            $nameAr = $nameEn;
        }

        $descriptionEn = trim((string) ($row['description_en'] ?? ''));
        $descriptionAr = trim((string) ($row['description_ar'] ?? $descriptionEn));

        $brandId = ImportRelationResolver::resolveBrandId($row['brand_id'] ?? null);

        $product = [
            'type' => strtolower(trim((string) ($row['type'] ?? 'simple'))) ?: 'simple',
            'name' => [
                'en' => $nameEn,
                'ar' => $nameAr,
            ],
            'description' => [
                'en' => $descriptionEn,
                'ar' => $descriptionAr,
            ],
            'thumbnail' => $this->nullableString($row['thumbnail'] ?? $row['thumbnail_url'] ?? null),
            'sku' => $this->resolveSku($row, $nameEn),
            'slug' => Product::ensureUniqueSlug(
                ImportSlugNormalizer::fromNameSource(
                    $this->nullableString($row['slug'] ?? null),
                    $nameEn ?: $nameAr ?: 'product',
                ),
            ),
            'price' => (float) ($row['price'] ?? 0),
            'stock' => $this->nullableInt($row['stock'] ?? null),
            'is_in_stock' => ImportBooleanParser::parse($row['is_in_stock'] ?? null, true),
            'discount' => isset($row['discount']) && $row['discount'] !== null && $row['discount'] !== '' ? (float) $row['discount'] : null,
            'discount_type' => $this->nullableString($row['discount_type'] ?? null),
            'is_active' => ImportBooleanParser::parse($row['is_active'] ?? null, true),
            'is_featured' => ImportBooleanParser::parse($row['is_featured'] ?? null, false),
            'is_new' => ImportBooleanParser::parse($row['is_new'] ?? null, false),
            'is_approved' => ImportBooleanParser::parse($row['is_approved'] ?? null, true),
            'is_bookable' => ImportBooleanParser::parse($row['is_bookable'] ?? null, false),
            'brand_id' => $brandId,
        ];

        $categoriesValue = $row['category_ids'] ?? $row['categories'] ?? null;
        $syncCategories = ProductSpreadsheetColumns::categoriesColumnProvided($row);

        $unitCode = strtolower(trim((string) ($row['unit_code'] ?? 'piece')));
        if ($unitCode === '') {
            $unitCode = 'piece';
        }

        return [
            'product' => $product,
            'category_ids' => ImportRelationResolver::resolveCategoryIds($categoriesValue),
            'sync_categories' => $syncCategories,
            'import_unit' => [
                'unit_code' => $unitCode,
                'factor' => max(1, (int) ($row['unit_factor'] ?? 1)),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveSku(array $row, string $nameEn): string
    {
        $sku = trim((string) ($row['sku'] ?? ''));

        if ($sku !== '') {
            return $sku;
        }

        return $this->generateUniqueSku();
    }

    private function generateUniqueSku(): string
    {
        $sku = 'PRD-'.strtoupper(Str::random(8));
        $counter = 1;

        while (Product::withTrashed()->where('sku', $sku)->exists()) {
            $sku = 'PRD-'.strtoupper(Str::random(8)).'-'.$counter;
            $counter++;
        }

        return $sku;
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
