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
            $table->unsignedTinyInteger('bogo_buy_quantity')->default(1)->after('value');
            $table->unsignedTinyInteger('bogo_bonus_quantity')->default(1)->after('bogo_buy_quantity');
            $table->string('bogo_bonus_discount_type', 20)->nullable()->after('bogo_bonus_quantity');
            $table->decimal('bogo_bonus_discount_value', 10, 2)->nullable()->after('bogo_bonus_discount_type');
        });

        foreach (DB::table('offers')->where('type', 'bogo')->get() as $offer) {
            DB::table('offers')->where('id', $offer->id)->update([
                'bogo_buy_quantity' => 1,
                'bogo_bonus_quantity' => max(1, (int) $offer->value),
                'bogo_bonus_discount_type' => 'percentage',
                'bogo_bonus_discount_value' => 100,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'bogo_buy_quantity',
                'bogo_bonus_quantity',
                'bogo_bonus_discount_type',
                'bogo_bonus_discount_value',
            ]);
        });
    }
};
