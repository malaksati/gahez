<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->nullable()->change();
            $table->boolean('is_in_stock')->default(true)->after('stock');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('stock')->nullable()->change();
            $table->boolean('is_in_stock')->default(true)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_in_stock');
            $table->integer('stock')->default(0)->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('is_in_stock');
            $table->integer('stock')->default(0)->change();
        });
    }
};
