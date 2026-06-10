<?php

namespace App\V1\DataTransfer\Support;

final class ProductSpreadsheetColumns
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
            'description_en',
            'description_ar',
            'type',
            'sku',
            'slug',
            'price',
            'stock',
            'is_in_stock',
            'sort_order',
            'discount',
            'discount_type',
            'thumbnail',
            'brand_id',
            'brand_name_snapshot',
            'category_ids',
            'is_active',
            'is_featured',
            'is_new',
            'is_approved',
            'is_bookable',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function canonicalizeRow(array $row): array
    {
        $row = SpreadsheetHeaderNormalizer::applyAliases($row, [
            'sku' => ['s_k_u'],
            'name_en' => ['name(_e_n)'],
            'name_ar' => ['name(_a_r)'],
            'description_en' => ['description(_e_n)'],
            'description_ar' => ['description(_a_r)'],
            'thumbnail' => ['thumbnail_url'],
            'thumbnail_url' => ['thumbnail_url'],
            'is_in_stock' => ['in_stock'],
            'sort_order' => ['sort', 'display_order'],
            'image_urls' => ['image_u_r_ls', 'image_urls'],
            'categories' => ['categories', 'category'],
            'category_ids' => ['category_ids'],
            'brand_id' => ['brand_id', 'vendor_id'],
            'is_active' => ['status', 'is_active'],
            'is_featured' => ['featured', 'is_featured'],
            'is_approved' => ['approved', 'is_approved'],
            'is_new' => ['is_new'],
            'is_approved' => ['is_approved'],
            'is_bookable' => ['is_bookable'],
        ]);

        return SpreadsheetImportRow::withoutGeneratedIds($row);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function categoriesColumnProvided(array $row): bool
    {
        return array_key_exists('categories', $row) || array_key_exists('category_ids', $row);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function categoriesRawValue(array $row): string
    {
        return trim((string) ($row['category_ids'] ?? $row['categories'] ?? ''));
    }
}
