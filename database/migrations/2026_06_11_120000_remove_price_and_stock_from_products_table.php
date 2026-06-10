<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'price')) {
            return;
        }

        if (! Schema::hasTable('product_units')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn(['price', 'stock']);
            });

            return;
        }

        $pieceUnitId = DB::table('units')->where('code', 'piece')->value('id');

        DB::table('products')->orderBy('id')->chunk(100, function ($products) use ($pieceUnitId) {
            foreach ($products as $product) {
                if ($product->type !== 'simple') {
                    continue;
                }

                $hasUnits = DB::table('product_units')->where('product_id', $product->id)->exists();

                if (! $hasUnits && $pieceUnitId) {
                    DB::table('product_units')->insert([
                        'product_id' => $product->id,
                        'unit_id' => $pieceUnitId,
                        'sku' => null,
                        'price' => $product->price,
                        'stock' => $product->stock,
                        'is_in_stock' => (bool) ($product->is_in_stock ?? true),
                        'factor' => 1,
                        'discount' => $product->discount,
                        'discount_type' => $product->discount_type,
                        'is_default' => true,
                        'sort_order' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    continue;
                }

                $defaultUnitId = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->where('is_default', true)
                    ->value('id')
                    ?? DB::table('product_units')
                        ->where('product_id', $product->id)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->value('id');

                if (! $defaultUnitId) {
                    continue;
                }

                $unitRow = DB::table('product_units')->where('id', $defaultUnitId)->first();

                if (! $unitRow) {
                    continue;
                }

                DB::table('product_units')
                    ->where('id', $defaultUnitId)
                    ->update([
                        'price' => $unitRow->price ?? $product->price,
                        'stock' => $unitRow->stock ?? $product->stock,
                        'updated_at' => now(),
                    ]);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasIndex('products', 'products_price_index')) {
                $table->dropIndex('products_price_index');
            }
            $table->dropColumn(['price', 'stock']);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'price')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('slug');
            $table->integer('stock')->nullable()->after('price');
        });

        if (! Schema::hasTable('product_units')) {
            return;
        }

        DB::table('products')->orderBy('id')->chunk(100, function ($products) {
            foreach ($products as $product) {
                if ($product->type === 'simple') {
                    $unit = DB::table('product_units')
                        ->where('product_id', $product->id)
                        ->where('is_default', true)
                        ->first()
                        ?? DB::table('product_units')
                            ->where('product_id', $product->id)
                            ->orderBy('sort_order')
                            ->orderBy('id')
                            ->first();

                    if ($unit) {
                        DB::table('products')->where('id', $product->id)->update([
                            'price' => $unit->price,
                            'stock' => $unit->stock,
                        ]);
                    }

                    continue;
                }

                $minPrice = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->min('price');

                $stockSum = DB::table('product_variants')
                    ->where('product_id', $product->id)
                    ->whereNotNull('stock')
                    ->sum('stock');

                DB::table('products')->where('id', $product->id)->update([
                    'price' => $minPrice ?? 0,
                    'stock' => $stockSum ?: null,
                ]);
            }
        });
    }
};
