<?php

namespace App\V1\DataTransfer\Support;

use App\Models\Variant;
use App\Models\VariantOption;
use Illuminate\Support\Collection;

final class VariantSpreadsheetColumns
{
    /**
     * @return list<string>
     */
    public static function headings(): array
    {
        return [
            'id',
            'name_en',
            'name_ar',
            'is_required',
            'is_active',
            'option_id',
            'option_name_en',
            'option_name_ar',
            'option_code',
        ];
    }

    /**
     * @return list<mixed>
     */
    public static function mapVariantOnly(Variant $variant): array
    {
        return [
            $variant->id,
            $variant->getTranslation('name', 'en', false),
            $variant->getTranslation('name', 'ar', false),
            $variant->is_required ? 1 : 0,
            $variant->is_active ? 1 : 0,
            null,
            null,
            null,
            null,
        ];
    }

    /**
     * @return list<mixed>
     */
    public static function mapVariantWithOption(Variant $variant, VariantOption $option): array
    {
        return [
            $variant->id,
            $variant->getTranslation('name', 'en', false),
            $variant->getTranslation('name', 'ar', false),
            $variant->is_required ? 1 : 0,
            $variant->is_active ? 1 : 0,
            $option->id,
            $option->getTranslation('name', 'en', false),
            $option->getTranslation('name', 'ar', false),
            $option->code,
        ];
    }

    /**
     * @param  Collection<int, Variant>  $variants
     * @return Collection<int, array<int, mixed>>
     */
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function canonicalizeRow(array $row): array
    {
        $row = SpreadsheetHeaderNormalizer::applyAliases($row, [
            'name_en' => ['name(_e_n)'],
            'name_ar' => ['name(_a_r)'],
            'is_active' => ['status'],
            'is_required' => ['required'],
            'option_name_en' => ['option_name(_e_n)'],
            'option_name_ar' => ['option_name(_a_r)'],
        ]);

        return SpreadsheetImportRow::withoutGeneratedVariantOptionIds(
            SpreadsheetImportRow::withoutGeneratedIds($row),
        );
    }

    /**
     * @param  Collection<int, Variant>  $variants
     * @return Collection<int, array<int, mixed>>
     */
    public static function flattenVariants(Collection $variants): Collection
    {
        return $variants->flatMap(function (Variant $variant) {
            if ($variant->options->isEmpty()) {
                return [self::mapVariantOnly($variant)];
            }

            return $variant->options->map(
                fn (VariantOption $option) => self::mapVariantWithOption($variant, $option),
            );
        })->values();
    }
}
