<?php

namespace App\V1\DataTransfer\Imports;

use App\V1\DataTransfer\Support\ImportBooleanParser;
use App\V1\DataTransfer\Support\ImportDisplayNameNormalizer;
use App\V1\DataTransfer\Support\ImportSlugNormalizer;
use App\V1\DataTransfer\Support\VariantSpreadsheetColumns;

final class VariantRowMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array{variant: array<string, mixed>, option: ?array<string, mixed>}
     */
    public function toImportRow(array $row): array
    {
        $row = VariantSpreadsheetColumns::canonicalizeRow($row);

        $nameEn = ImportDisplayNameNormalizer::name((string) ($row['name_en'] ?? ''));
        $nameAr = ImportDisplayNameNormalizer::name((string) ($row['name_ar'] ?? $nameEn), 'ar');
        if ($nameAr === '' && $nameEn !== '') {
            $nameAr = $nameEn;
        }

        $variant = [
            'id' => $this->nullableInt($row['id'] ?? null),
            'name' => [
                'en' => $nameEn,
                'ar' => $nameAr,
            ],
            'is_required' => ImportBooleanParser::parse($row['is_required'] ?? null, false),
            'is_active' => ImportBooleanParser::parse($row['is_active'] ?? null, true),
        ];

        if (! $this->rowHasOptionData($row)) {
            return ['variant' => $variant, 'option' => null];
        }

        $optionNameEn = ImportDisplayNameNormalizer::name((string) ($row['option_name_en'] ?? ''));
        $optionNameAr = ImportDisplayNameNormalizer::name((string) ($row['option_name_ar'] ?? $optionNameEn), 'ar');
        if ($optionNameAr === '' && $optionNameEn !== '') {
            $optionNameAr = $optionNameEn;
        }

        return [
            'variant' => $variant,
            'option' => [
                'id' => $this->nullableInt($row['option_id'] ?? null),
                'name' => [
                    'en' => $optionNameEn,
                    'ar' => $optionNameAr,
                ],
                'code' => $this->normalizeOptionCode($row, $optionNameEn),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowHasOptionData(array $row): bool
    {
        foreach (['option_id', 'option_name_en', 'option_name_ar', 'option_code'] as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function normalizeOptionCode(array $row, string $optionNameEn): string
    {
        $code = trim((string) ($row['option_code'] ?? ''));

        if ($code === '') {
            return '';
        }

        return ImportSlugNormalizer::normalize($code, $optionNameEn);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
