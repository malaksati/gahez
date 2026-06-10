<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'Gahez', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'KWD', 'type' => 'string'],
            ['key' => 'cashback_percentage', 'value' => '5', 'type' => 'number'],
            ['key' => 'point_to_value', 'value' => '0.01', 'type' => 'number'],
            ['key' => 'shipping_price_per_km', 'value' => '0.5', 'type' => 'number'],
            ['key' => 'report_hero_order_amount', 'value' => '100', 'type' => 'number'],
            ['key' => 'report_lower_value_order_amount', 'value' => '20', 'type' => 'number'],
            ['key' => 'store_primary_color', 'value' => '#faad28', 'type' => 'string'],
            ['key' => 'store_secondary_color', 'value' => '#f8a713', 'type' => 'string'],
            ['key' => 'store_category_layout', 'value' => 'horizontal', 'type' => 'string'],
            ['key' => 'store_product_layout', 'value' => 'vertical', 'type' => 'string'],
            ['key' => 'store_font_family', 'value' => 'Cairo', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }
    }
}
