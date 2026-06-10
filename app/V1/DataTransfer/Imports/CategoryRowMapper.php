<?php

namespace App\V1\DataTransfer\Imports;

use App\V1\DataTransfer\Support\CategorySpreadsheetColumns;
use App\V1\DataTransfer\Support\ImportBooleanParser;
use App\V1\DataTransfer\Support\ImportDisplayNameNormalizer;
use App\V1\DataTransfer\Support\ImportRelationResolver;
use App\V1\DataTransfer\Support\ImportSlugNormalizer;

final class CategoryRowMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function toPayload(array $row): array
    {
        $row = CategorySpreadsheetColumns::canonicalizeRow($row);

        $nameEn = ImportDisplayNameNormalizer::name((string) ($row['name_en'] ?? ''));
        $nameAr = ImportDisplayNameNormalizer::name((string) ($row['name_ar'] ?? $nameEn), 'ar');
        if ($nameAr === '' && $nameEn !== '') {
            $nameAr = $nameEn;
        }

        $slug = ImportSlugNormalizer::fromNameSource(
            isset($row['slug']) && trim((string) $row['slug']) !== '' ? (string) $row['slug'] : null,
            $nameEn,
        );

        return [
            'name' => [
                'en' => $nameEn,
                'ar' => $nameAr,
            ],
            'image' => $this->nullableString($row['image'] ?? null),
            'is_active' => ImportBooleanParser::parse($row['is_active'] ?? null, true),
            'is_featured' => ImportBooleanParser::parse($row['is_featured'] ?? null, false),
            'sort_order' => $this->nullableInt($row['sort_order'] ?? null) ?? 0,
            'slug' => $slug,
            'parent_id' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function extractParentReference(array $row): ?string
    {
        $row = CategorySpreadsheetColumns::canonicalizeRow($row);

        if ($this->hasExplicitParentId($row)) {
            return null;
        }

        $reference = trim((string) ($row['parent_slug'] ?? ''));

        if ($reference === '' || $this->isEmptyParentReference($reference)) {
            return null;
        }

        return $reference;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function extractExplicitParentId(array $row): ?int
    {
        $row = CategorySpreadsheetColumns::canonicalizeRow($row);

        if (! $this->hasExplicitParentId($row)) {
            return null;
        }

        return (int) $row['parent_id'];
    }

    public function resolveParentIdFromReference(string $parentReference, ?string $childSlug = null): ?int
    {
        return ImportRelationResolver::resolveCategoryParentId($parentReference, $childSlug);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function hasExplicitParentId(array $row): bool
    {
        return isset($row['parent_id']) && $row['parent_id'] !== '' && $row['parent_id'] !== null;
    }

    private function isEmptyParentReference(string $reference): bool
    {
        $normalized = strtolower(trim($reference));

        return in_array($normalized, [
            '',
            'root',
            'none',
            'null',
            '-',
            '—',
            'n/a',
            'na',
        ], true);
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
