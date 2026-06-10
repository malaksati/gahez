<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            ['key' => 'store_primary_color', 'value' => '#faad28', 'type' => 'string'],
            ['key' => 'store_secondary_color', 'value' => '#f8a713', 'type' => 'string'],
            ['key' => 'store_category_layout', 'value' => 'horizontal', 'type' => 'string'],
            ['key' => 'store_product_layout', 'value' => 'vertical', 'type' => 'string'],
            ['key' => 'store_font_family', 'value' => 'Cairo', 'type' => 'string'],
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
            'store_primary_color',
            'store_secondary_color',
            'store_category_layout',
            'store_product_layout',
            'store_font_family',
        ])->delete();
    }
};
