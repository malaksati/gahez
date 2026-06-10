<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'processing',
            'ready_for_delivery',
            'shipped',
            'delivered',
            'cancelled',
            'refunded'
        ) DEFAULT 'pending'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'refunded'
        ) DEFAULT 'pending'");
        });
    }
};
