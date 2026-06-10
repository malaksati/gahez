<?php

namespace App\V1\DataTransfer\Imports;

use App\V1\DataTransfer\Support\ImportDisplayNameNormalizer;
use App\V1\DataTransfer\Support\ImportSlugNormalizer;

final class VariantOptionRowMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function toPayload(array $row): array
    {
        $nameEn = ImportDisplayNameNormalizer::name((string) ($row['name_en'] ?? ''));
        $nameAr = ImportDisplayNameNormalizer::name((string) ($row['name_ar'] ?? $nameEn), 'ar');
        if ($nameAr === '' && $nameEn !== '') {
            $nameAr = $nameEn;
        }

        return [
            'id' => $this->nullableInt($row['id'] ?? null),
            'variant_id' => $this->resolveVariantId($row),
            'name' => [
                'en' => $nameEn,
                'ar' => $nameAr,
            ],
            'code' => $this->normalizeCode($row, $nameEn),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveVariantId(array $row): ?int
    {
        if (isset($row['variant_id']) && $row['variant_id'] !== '') {
            return (int) $row['variant_id'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function normalizeCode(array $row, string $nameEn): string
    {
        $code = trim((string) ($row['code'] ?? ''));

        if ($code === '') {
            return '';
        }

        return ImportSlugNormalizer::normalize($code, $nameEn);
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
