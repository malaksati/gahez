<?php

namespace Tests\Unit;

use App\Models\ProductUnit;
use App\Models\Unit;
use App\V1\Services\ProductUnitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOfferFixtures;
use Tests\TestCase;

class ProductUnitServiceTest extends TestCase
{
    use CreatesOfferFixtures, RefreshDatabase;

    public function test_sync_updates_existing_row_by_unit_id_when_id_missing(): void
    {
        $product = $this->createProduct(['price' => 10]);
        $unit = Unit::query()->where('code', 'box')->firstOrFail();

        $existing = ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('unit_id', $unit->id)
            ->first();

        if (! $existing) {
            $existing = ProductUnit::query()->create([
                'product_id' => $product->id,
                'unit_id' => $unit->id,
                'price' => 50,
                'factor' => 1,
                'is_default' => false,
                'is_active' => true,
                'is_in_stock' => true,
                'sort_order' => 1,
            ]);
        }

        app(ProductUnitService::class)->syncForProduct($product, [
            [
                'unit_id' => $unit->id,
                'price' => 140,
                'factor' => 30,
                'is_default' => true,
                'is_active' => true,
                'is_in_stock' => true,
            ],
        ]);

        $this->assertSame(1, ProductUnit::query()->where('product_id', $product->id)->where('unit_id', $unit->id)->count());
        $this->assertSame(140.0, (float) $existing->fresh()->price);
        $this->assertSame(30, (int) $existing->fresh()->factor);
    }

    public function test_sync_generates_unit_sku_from_product_sku_and_unit_code(): void
    {
        $product = $this->createProduct(['sku' => 'PRD-0099', 'price' => 10]);
        $unit = Unit::query()->where('code', 'piece')->firstOrFail();

        app(ProductUnitService::class)->syncForProduct($product, [
            [
                'unit_id' => $unit->id,
                'price' => 10,
                'factor' => 1,
                'is_default' => true,
                'is_active' => true,
                'is_in_stock' => true,
            ],
        ]);

        $productUnit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('unit_id', $unit->id)
            ->first();

        $this->assertNotNull($productUnit);
        $this->assertSame('PRD-0099-piece', $productUnit->sku);
    }

    public function test_sync_with_no_rows_creates_default_piece_unit(): void
    {
        $product = $this->createProduct(['price' => 10, 'sku' => 'PRD-0100']);
        $pieceUnitId = Unit::query()->where('code', 'piece')->value('id');

        ProductUnit::query()->where('product_id', $product->id)->delete();

        app(ProductUnitService::class)->syncForProduct($product, []);

        $productUnit = ProductUnit::query()
            ->where('product_id', $product->id)
            ->where('unit_id', $pieceUnitId)
            ->first();

        $this->assertNotNull($productUnit);
        $this->assertTrue($productUnit->is_default);
        $this->assertSame('PRD-0100-piece', $productUnit->sku);
    }
}
