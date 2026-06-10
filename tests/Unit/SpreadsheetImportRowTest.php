<?php

namespace Tests\Unit;

use App\V1\DataTransfer\Support\CategorySpreadsheetColumns;
use App\V1\DataTransfer\Support\ProductSpreadsheetColumns;
use App\V1\DataTransfer\Support\VariantSpreadsheetColumns;
use PHPUnit\Framework\TestCase;

class SpreadsheetImportRowTest extends TestCase
{
    public function test_product_canonicalize_strips_id_column(): void
    {
        $row = ProductSpreadsheetColumns::canonicalizeRow([
            'id' => '42',
            'name_en' => 'Widget',
            'sku' => 'W-1',
        ]);

        $this->assertArrayNotHasKey('id', $row);
        $this->assertSame('Widget', $row['name_en']);
    }

    public function test_category_canonicalize_strips_id_column(): void
    {
        $row = CategorySpreadsheetColumns::canonicalizeRow([
            'id' => '99',
            'name_en' => 'Books',
        ]);

        $this->assertArrayNotHasKey('id', $row);
        $this->assertSame('Books', $row['name_en']);
    }

    public function test_variant_canonicalize_strips_id_and_option_id_columns(): void
    {
        $row = VariantSpreadsheetColumns::canonicalizeRow([
            'id' => '7',
            'option_id' => '13',
            'name_en' => 'Color',
        ]);

        $this->assertArrayNotHasKey('id', $row);
        $this->assertArrayNotHasKey('option_id', $row);
    }

    public function test_product_headings_include_new_columns(): void
    {
        $headings = ProductSpreadsheetColumns::headings();

        $this->assertContains('unit_code', $headings);
        $this->assertContains('unit_factor', $headings);
        $this->assertContains('is_in_stock', $headings);
    }

    public function test_category_headings_include_sort_order(): void
    {
        $this->assertContains('sort_order', CategorySpreadsheetColumns::headings());
    }

    public function test_product_canonicalize_maps_unit_and_stock_aliases(): void
    {
        $row = ProductSpreadsheetColumns::canonicalizeRow([
            'unit_qty' => 12,
            'in_stock' => 1,
        ]);

        $this->assertSame(12, $row['unit_factor']);
        $this->assertSame(1, $row['is_in_stock']);
    }
}
