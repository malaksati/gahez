<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_units', 'product_variant_id')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->foreignId('product_variant_id')
                    ->nullable()
                    ->after('unit_id')
                    ->constrained('product_variants')
                    ->nullOnDelete();
            });
        }

        if (! $this->indexExists('product_units', 'product_units_product_id_index')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->index('product_id', 'product_units_product_id_index');
            });
        }

        if ($this->indexExists('product_units', 'product_units_product_id_unit_id_unique')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->dropUnique(['product_id', 'unit_id']);
            });
        }

        if (! $this->indexExists('product_units', 'product_units_product_unit_variant_unique')) {
            Schema::table('product_units', function (Blueprint $table) {
                $table->unique(
                    ['product_id', 'unit_id', 'product_variant_id'],
                    'product_units_product_unit_variant_unique',
                );
            });
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $definition) {
            if (($definition['name'] ?? '') === $index) {
                return true;
            }
        }

        return false;
    }

    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropUnique('product_units_product_unit_variant_unique');
            $table->unique(['product_id', 'unit_id']);
        });

        Schema::table('product_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
        });
    }
};
