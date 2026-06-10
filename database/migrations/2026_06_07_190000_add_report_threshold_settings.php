<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['key' => 'report_hero_order_amount', 'value' => '100', 'type' => 'number'],
            ['key' => 'report_lower_value_order_amount', 'value' => '20', 'type' => 'number'],
        ] as $setting) {
            DB::table('settings')->insertOrIgnore(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'report_hero_order_amount',
            'report_lower_value_order_amount',
        ])->delete();
    }
};
