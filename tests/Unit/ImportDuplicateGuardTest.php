<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantOption;
use App\V1\DataTransfer\Support\ImportDuplicateGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportDuplicateGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_skips_category_when_slug_exists_in_database(): void
    {
        $category = Category::query()->create([
            'name' => ['en' => 'Electronics', 'ar' => 'إلكترونيات'],
        ]);
        $category->forceFill(['slug' => 'electronics'])->saveQuietly();

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipCategory([
            'slug' => 'electronics',
            'name' => ['en' => 'Other', 'ar' => 'أخرى'],
        ]));
    }

    public function test_skips_duplicate_category_slug_within_same_file(): void
    {
        $guard = new ImportDuplicateGuard;

        $payload = [
            'slug' => 'books',
            'name' => ['en' => 'Books', 'ar' => 'كتب'],
        ];

        $this->assertFalse($guard->shouldSkipCategory($payload));
        $this->assertTrue($guard->shouldSkipCategory($payload));
    }

    public function test_skips_product_by_sku_slug_or_name(): void
    {
        $brandId = Brand::query()->create([
            'name' => ['en' => 'Brand', 'ar' => 'ماركة'],
        ])->id;

        Product::query()->create([
            'type' => 'simple',
            'sku' => 'SKU-100',
            'slug' => 'widget',
            'name' => ['en' => 'Widget', 'ar' => 'أداة'],
            'description' => ['en' => '', 'ar' => ''],
            'price' => 10,
            'stock' => 0,
            'brand_id' => $brandId,
        ]);

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipProduct([
            'sku' => 'SKU-100',
            'slug' => 'other',
            'name' => ['en' => 'Other', 'ar' => 'أخرى'],
        ]));

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipProduct([
            'sku' => 'SKU-NEW',
            'slug' => 'widget',
            'name' => ['en' => 'Other', 'ar' => 'أخرى'],
        ]));

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipProduct([
            'sku' => 'SKU-NEW',
            'slug' => 'new-slug',
            'name' => ['en' => 'Widget', 'ar' => 'أداة'],
        ]));
    }

    public function test_skips_variant_when_name_exists_but_allows_multiple_rows_for_new_variant_options(): void
    {
        Variant::query()->create([
            'name' => ['en' => 'Color', 'ar' => 'لون'],
        ]);

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipVariant([
            'name' => ['en' => 'Color', 'ar' => 'لون'],
        ]));

        $guard = new ImportDuplicateGuard;

        $this->assertFalse($guard->shouldSkipVariant([
            'name' => ['en' => 'Size', 'ar' => 'مقاس'],
        ]));
        $this->assertFalse($guard->shouldSkipVariant([
            'name' => ['en' => 'Size', 'ar' => 'مقاس'],
        ]));
    }

    public function test_skips_variant_option_when_code_exists(): void
    {
        $variantId = Variant::query()->create([
            'name' => ['en' => 'Color', 'ar' => 'لون'],
        ])->id;

        VariantOption::query()->create([
            'variant_id' => $variantId,
            'name' => ['en' => 'Red', 'ar' => 'أحمر'],
            'code' => 'red',
        ]);

        $guard = new ImportDuplicateGuard;

        $this->assertTrue($guard->shouldSkipVariantOption('red'));
        $this->assertTrue($guard->shouldSkipVariantOption('red'));
    }
}
