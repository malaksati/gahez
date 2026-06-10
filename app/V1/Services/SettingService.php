<?php

namespace App\V1\Services;

use App\Models\Setting;
use App\V1\Support\StoreTheme;
use App\V1\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * @param  array{app_name: string, currency: string, cashback_percentage?: float|int|string, point_to_value?: float|int|string, report_hero_order_amount?: float|int|string, report_lower_value_order_amount?: float|int|string, app_logo?: UploadedFile|null}  $data
     */
    public function update(array $data): void
    {
        $this->persist('app_name', $data['app_name'], 'string');
        setting_forget('app_name');

        $this->persist('currency', strtoupper(trim($data['currency'])), 'string');
        setting_forget('currency');

        if (array_key_exists('cashback_percentage', $data)) {
            $this->persist('cashback_percentage', max(0, (float) $data['cashback_percentage']), 'number');
            setting_forget('cashback_percentage');
        }

        if (array_key_exists('point_to_value', $data)) {
            $this->persist('point_to_value', max(0, (float) $data['point_to_value']), 'number');
            setting_forget('point_to_value');
        }

        if (array_key_exists('report_hero_order_amount', $data)) {
            $this->persist('report_hero_order_amount', max(0, (float) $data['report_hero_order_amount']), 'number');
            setting_forget('report_hero_order_amount');
        }

        if (array_key_exists('report_lower_value_order_amount', $data)) {
            $this->persist('report_lower_value_order_amount', max(0, (float) $data['report_lower_value_order_amount']), 'number');
            setting_forget('report_lower_value_order_amount');
        }

        if (
            isset($data['app_logo'])
            && $data['app_logo'] instanceof UploadedFile
            && $data['app_logo']->isValid()
        ) {
            $this->storeImage('app_logo', $data['app_logo']);
        }
    }

    /**
     * @param  array<string, string>  $data
     */
    public function updateStoreTheme(array $data): void
    {
        $theme = StoreTheme::sanitizeInput([
            'primary_color' => $data['store_primary_color'] ?? null,
            'secondary_color' => $data['store_secondary_color'] ?? null,
            'category_layout' => $data['store_category_layout'] ?? null,
            'product_layout' => $data['store_product_layout'] ?? null,
            'font_family' => $data['store_font_family'] ?? null,
        ]);

        $this->persist('store_primary_color', $theme['primary_color'], 'string');
        setting_forget('store_primary_color');

        $this->persist('store_secondary_color', $theme['secondary_color'], 'string');
        setting_forget('store_secondary_color');

        $this->persist('store_category_layout', $theme['category_layout'], 'string');
        setting_forget('store_category_layout');

        $this->persist('store_product_layout', $theme['product_layout'], 'string');
        setting_forget('store_product_layout');

        $this->persist('store_font_family', $theme['font_family'], 'string');
        setting_forget('store_font_family');
    }

    private function persist(string $key, mixed $value, string $type): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type],
        );
    }

    private function storeImage(string $key, UploadedFile $file): void
    {
        $previous = Setting::query()->where('key', $key)->value('value');

        if ($previous && Storage::disk('public')->exists($previous)) {
            Storage::disk('public')->delete($previous);
        }

        $path = UploadStorage::store($file, 'settings');

        $this->persist($key, $path, 'image');
        setting_forget($key);
    }
}
