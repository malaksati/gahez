<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_day', 16)->nullable()->after('shipping_address_snapshot');
            $table->boolean('is_fast_shipping')->default(false)->after('shipping_day');
            $table->decimal('fast_shipping_fee', 10, 2)->default(0)->after('is_fast_shipping');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_day', 'is_fast_shipping', 'fast_shipping_fee']);
        });
    }
};
