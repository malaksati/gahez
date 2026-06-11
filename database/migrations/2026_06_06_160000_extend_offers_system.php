<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percentage', 'bogo', 'threshold_gift', 'free_delivery'])->change();
            $table->decimal('min_cart_amount', 10, 2)->nullable()->after('value');
            $table->unsignedInteger('max_discounted_quantity')->nullable()->after('min_cart_amount');
            $table->boolean('ends_when_out_of_stock')->default(false)->after('max_discounted_quantity');
            $table->timestamp('start_date')->nullable()->change();
            $table->timestamp('end_date')->nullable()->change();
            $table->unsignedBigInteger('offerable_id')->nullable()->change();
            $table->string('offerable_type')->nullable()->change();
        });

        Schema::create('offer_reward_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['offer_id', 'product_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('gift_offer_id')->nullable()->after('notes')->constrained('offers')->nullOnDelete();
            $table->foreignId('gift_product_id')->nullable()->after('gift_offer_id')->constrained('products')->nullOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_gift')->default(false)->after('unit_price');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'is_gift')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('is_gift');
            });
        }

        if (Schema::hasColumn('orders', 'gift_product_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('gift_product_id');
            });
        }

        if (Schema::hasColumn('orders', 'gift_offer_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('gift_offer_id');
            });
        }

        Schema::dropIfExists('offer_reward_products');

        DB::table('offers')
            ->whereNotIn('type', ['fixed', 'percentage'])
            ->update(['type' => 'fixed']);

        $offerColumnsToDrop = array_values(array_filter(
            ['min_cart_amount', 'max_discounted_quantity', 'ends_when_out_of_stock'],
            static fn (string $column): bool => Schema::hasColumn('offers', $column),
        ));

        if ($offerColumnsToDrop !== []) {
            Schema::table('offers', function (Blueprint $table) use ($offerColumnsToDrop) {
                $table->dropColumn($offerColumnsToDrop);
            });
        }

        $fallbackProductId = DB::table('products')->orderBy('id')->value('id');

        if ($fallbackProductId !== null) {
            DB::table('offers')
                ->whereNull('offerable_id')
                ->update([
                    'offerable_id' => $fallbackProductId,
                    'offerable_type' => 'App\\Models\\Product',
                ]);
        }

        DB::table('offers')
            ->whereNull('offerable_type')
            ->update(['offerable_type' => 'App\\Models\\Product']);

        Schema::table('offers', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percentage'])->change();
            $table->unsignedBigInteger('offerable_id')->nullable(false)->change();
            $table->string('offerable_type')->nullable(false)->change();
        });
    }
};
