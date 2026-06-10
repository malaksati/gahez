<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('points')->default(0)->after('wallet');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('cashback_awarded_at')->nullable()->after('stock_deducted_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cashback_awarded_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
