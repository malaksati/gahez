<?php

namespace App\V1\Services;

use App\Models\Setting;
use App\V1\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    /**
     * @param  array{
     *     app_name: string,
     *     currency: string,
     *     cashback_percentage?: float|int|string,
     *     point_to_value?: float|int|string,
     *     shipping_price_per_km?: float|int|string,
     *     cart_min_line_count?: int|string,
     *     cart_min_subtotal?: float|int|string,
     *     fast_shipping_fee?: float|int|string,
     *     app_logo?: UploadedFile|null
     * }  $data
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

        if (array_key_exists('shipping_price_per_km', $data)) {
            $this->persist('shipping_price_per_km', max(0, (float) $data['shipping_price_per_km']), 'number');
            setting_forget('shipping_price_per_km');
        }

        if (array_key_exists('cart_min_line_count', $data)) {
            $this->persist('cart_min_line_count', max(0, (int) $data['cart_min_line_count']), 'number');
            setting_forget('cart_min_line_count');
        }

        if (array_key_exists('cart_min_subtotal', $data)) {
            $this->persist('cart_min_subtotal', max(0, (float) $data['cart_min_subtotal']), 'number');
            setting_forget('cart_min_subtotal');
        }

        if (array_key_exists('fast_shipping_fee', $data)) {
            $this->persist('fast_shipping_fee', max(0, (float) $data['fast_shipping_fee']), 'number');
            setting_forget('fast_shipping_fee');
        }

        if (
            isset($data['app_logo'])
            && $data['app_logo'] instanceof UploadedFile
            && $data['app_logo']->isValid()
        ) {
            $this->storeImage('app_logo', $data['app_logo']);
        }
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
