<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')->where('key', 'standard_shipping_fee')->exists();

        if (! $exists) {
            $legacyFlat = DB::table('settings')->where('key', 'shipping_price_per_km')->value('value');

            DB::table('settings')->insert([
                'key' => 'standard_shipping_fee',
                'value' => is_numeric($legacyFlat) && (float) $legacyFlat > 0 ? (string) $legacyFlat : '5',
                'type' => 'number',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'standard_shipping_fee')->delete();
    }
};
