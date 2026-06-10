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
            ['key' => 'currency', 'value' => 'EGP', 'type' => 'string'],
            ['key' => 'cashback_percentage', 'value' => '5', 'type' => 'number'],
            ['key' => 'point_to_value', 'value' => '0.01', 'type' => 'number'],
            ['key' => 'shipping_price_per_km', 'value' => '0.5', 'type' => 'number'],
            ['key' => 'cart_min_line_count', 'value' => '0', 'type' => 'number'],
            ['key' => 'cart_min_subtotal', 'value' => '0', 'type' => 'number'],
            ['key' => 'fast_shipping_fee', 'value' => '0', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'type' => $setting['type']],
            );
        }
    }
}
