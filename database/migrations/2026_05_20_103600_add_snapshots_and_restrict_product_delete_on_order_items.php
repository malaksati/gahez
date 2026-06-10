<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('variant_id');
            $table->string('product_name_ar')->nullable()->after('product_name');
            $table->string('product_slug')->nullable()->after('product_name_ar');
            $table->string('product_sku')->nullable()->after('product_slug');
            $table->string('variant_name')->nullable()->after('product_sku');
            $table->string('variant_name_ar')->nullable()->after('variant_name');
            $table->string('variant_sku')->nullable()->after('variant_name_ar');

        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'product_name_ar',
                'product_slug',
                'product_sku',
                'variant_name',
                'variant_name_ar',
                'variant_sku',
            ]);
        });
    }
};
