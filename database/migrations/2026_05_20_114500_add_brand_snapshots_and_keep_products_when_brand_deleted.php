<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('brand_snapshot')->nullable()->after('brand_id');
            $table->json('category_snapshot')->nullable()->after('brand_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand_snapshot', 'category_snapshot']);
        });
    }
};
