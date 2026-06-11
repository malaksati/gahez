<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percentage', 'free_delivery'])->change();
            $table->unsignedInteger('usage_limit')->nullable()->after('usage_limit_per_user');
        });
    }

    public function down(): void
    {
        DB::table('coupons')
            ->where('type', 'free_delivery')
            ->update(['type' => 'fixed']);

        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percentage'])->change();
            $table->dropColumn('usage_limit');
        });
    }
};
