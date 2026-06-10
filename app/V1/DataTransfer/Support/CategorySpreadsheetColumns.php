<?php

namespace App\V1\DataTransfer\Support;

final class CategorySpreadsheetColumns
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
            'slug',
            'image',
            'is_active',
            'is_featured',
            'sort_order',
            'parent_id',
            'parent_slug',
        ];
    }

    /**
     * Map export / third-party spreadsheet keys to the canonical import keys.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function canonicalizeRow(array $row): array
    {
        $row = SpreadsheetHeaderNormalizer::applyAliases($row, [
            'name_en' => ['name(_e_n)', 'name_english', 'english_name'],
            'name_ar' => ['name(_a_r)', 'name_arabic', 'arabic_name'],
            'image' => ['image_url', 'image_u_r_l'],
            'is_active' => ['status'],
            'is_featured' => ['featured'],
            'sort_order' => ['sort', 'display_order'],
            'parent_slug' => ['parent_category', 'parent'],
        ]);

        return SpreadsheetImportRow::withoutGeneratedIds($row);
    }
}
