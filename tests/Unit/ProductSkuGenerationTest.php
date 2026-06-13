<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Product;
use App\V1\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_next_simple_sku_in_sequence(): void
    {
        Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'One', 'ar' => 'واحد'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-0007',
            'slug' => 'one',
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $this->createBrandId(),
        ]);

        $this->assertSame('PRD-0008', app(ProductService::class)->generateNextSimpleSku());
    }

    public function test_skips_skus_reserved_by_soft_deleted_products(): void
    {
        $brandId = $this->createBrandId();

        $deleted = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Deleted', 'ar' => 'محذوف'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-0008',
            'slug' => 'deleted-product',
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $brandId,
        ]);

        $deleted->delete();

        Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Active', 'ar' => 'نشط'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-0007',
            'slug' => 'active-product',
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $brandId,
        ]);

        $this->assertSame('PRD-0009', app(ProductService::class)->generateNextSimpleSku());
    }

    public function test_ensure_unique_slug_avoids_soft_deleted_products(): void
    {
        $brandId = $this->createBrandId();

        $deleted = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Fresh Eggs', 'ar' => 'بيض'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-0101',
            'slug' => 'fresh-eggs',
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $brandId,
        ]);

        $deleted->delete();

        $this->assertSame('fresh-eggs-2', Product::ensureUniqueSlug('fresh-eggs'));
    }

    public function test_is_sku_taken_includes_soft_deleted_products(): void
    {
        $brandId = $this->createBrandId();

        $deleted = Product::query()->create([
            'type' => 'simple',
            'name' => ['en' => 'Taken', 'ar' => 'مأخوذ'],
            'description' => ['en' => 'Desc', 'ar' => 'وصف'],
            'sku' => 'PRD-0042',
            'slug' => 'taken-sku',
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $brandId,
        ]);

        $deleted->delete();

        $this->assertTrue(Product::isSkuTaken('PRD-0042'));
    }

    protected function createBrandId(): int
    {
        return Brand::query()->create([
            'name' => ['en' => 'Brand', 'ar' => 'ماركة'],
            'is_active' => true,
        ])->id;
    }
}
