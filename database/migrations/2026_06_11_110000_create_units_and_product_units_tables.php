<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('code', 32)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->seedBaseUnits();

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->nullable();
            $table->boolean('is_in_stock')->default(true);
            $table->unsignedInteger('factor')->default(1);
            $table->decimal('discount', 10, 2)->nullable();
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                ->nullable()
                ->after('variant_id')
                ->constrained('product_units')
                ->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_unit_id')
                ->nullable()
                ->after('variant_id')
                ->constrained('product_units')
                ->nullOnDelete();
            $table->string('unit_name')->nullable()->after('variant_sku');
            $table->string('unit_name_ar')->nullable()->after('unit_name');
            $table->unsignedInteger('unit_factor')->nullable()->after('unit_name_ar');
        });

        $this->migrateProductUnitColumns();

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['unit', 'unit_quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('unit')->nullable()->after('stock');
            $table->unsignedInteger('unit_quantity')->default(1)->after('unit');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_unit_id');
            $table->dropColumn(['unit_name', 'unit_name_ar', 'unit_factor']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_unit_id');
        });

        Schema::dropIfExists('product_units');
        Schema::dropIfExists('units');
    }

    protected function seedBaseUnits(): void
    {
        $now = now();

        foreach ([
            ['code' => 'piece', 'name' => ['en' => 'piece', 'ar' => 'قطعة']],
            ['code' => 'bottle', 'name' => ['en' => 'bottle', 'ar' => 'زجاجة']],
            ['code' => 'carton', 'name' => ['en' => 'carton', 'ar' => 'كرتونة']],
            ['code' => 'box', 'name' => ['en' => 'box', 'ar' => 'صندوق']],
            ['code' => 'kg', 'name' => ['en' => 'kg', 'ar' => 'كجم']],
        ] as $unit) {
            DB::table('units')->insert([
                'name' => json_encode($unit['name'], JSON_UNESCAPED_UNICODE),
                'code' => $unit['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    protected function migrateProductUnitColumns(): void
    {
        if (! Schema::hasColumn('products', 'unit')) {
            return;
        }

        $now = now();
        $pieceUnitId = DB::table('units')->where('code', 'piece')->value('id');

        DB::table('products')->orderBy('id')->chunk(100, function ($products) use ($pieceUnitId, $now) {
            foreach ($products as $product) {
                $unitJson = $product->unit ? json_decode($product->unit, true) : null;
                $unitEn = is_array($unitJson) ? trim((string) ($unitJson['en'] ?? '')) : '';
                $unitAr = is_array($unitJson) ? trim((string) ($unitJson['ar'] ?? '')) : '';

                if ($unitEn !== '' || $unitAr !== '') {
                    $code = 'custom-'.$product->id;
                    $unitId = DB::table('units')->insertGetId([
                        'name' => json_encode([
                            'en' => $unitEn ?: $unitAr,
                            'ar' => $unitAr ?: $unitEn,
                        ], JSON_UNESCAPED_UNICODE),
                        'code' => $code,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $unitId = $pieceUnitId;
                }

                DB::table('product_units')->insert([
                    'product_id' => $product->id,
                    'unit_id' => $unitId,
                    'sku' => null,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'is_in_stock' => (bool) ($product->is_in_stock ?? true),
                    'factor' => max(1, (int) ($product->unit_quantity ?? 1)),
                    'discount' => $product->discount,
                    'discount_type' => $product->discount_type,
                    'is_default' => true,
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
};
