<?php

namespace Tests\Unit;

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
            'price' => 10,
            'stock' => 1,
            'is_active' => true,
            'is_approved' => true,
            'is_bookable' => true,
            'brand_id' => $this->createBrandId(),
        ]);

        $this->assertSame('PRD-0008', app(ProductService::class)->generateNextSimpleSku());
    }

    protected function createBrandId(): int
    {
        return \App\Models\Brand::query()->create([
            'name' => ['en' => 'Brand', 'ar' => 'ماركة'],
            'is_active' => true,
        ])->id;
    }
}
