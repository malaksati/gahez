<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('user_id');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_phone')->nullable()->after('customer_email');
            $table->longText('shipping_address_snapshot')->nullable()->after('address_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('line_discount', 12, 2)->default(0)->after('unit_price');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['address_id']);

            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('coupon_id')->nullable()->change();
            $table->foreignId('address_id')->nullable()->change();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['address_id']);

            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreignId('coupon_id')->nullable()->change();
            $table->foreignId('address_id')->nullable()->change();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('coupon_id')->references('id')->on('coupons')->cascadeOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['line_discount']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_address_snapshot',
            ]);
        });
    }
};
