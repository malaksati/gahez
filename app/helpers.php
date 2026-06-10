<?php

use App\Models\Setting;

if (! function_exists('setting_cache')) {
    /**
     * @return array<string, mixed>
     */
    function &setting_cache(): array
    {
        static $cache = [];

        return $cache;
    }
}

if (! function_exists('setting_forget')) {
    function setting_forget(?string $key = null): void
    {
        $cache = &setting_cache();

        if ($key === null) {
            $cache = [];

            return;
        }

        unset($cache[$key]);
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $cache = &setting_cache();

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            if (class_exists(Setting::class) && \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $value = Setting::query()->where('key', $key)->value('value');

                if ($value !== null) {
                    return $cache[$key] = $value;
                }
            }
        } catch (Throwable) {
            // Database may be unavailable during install.
        }

        return $cache[$key] = match ($key) {
            'app_name' => config('app.name'),
            'currency' => config('app.currency', ''),
            default => $default,
        };
    }
}

if (! function_exists('app_currency')) {
    function app_currency(): string
    {
        return (string) setting('currency', config('app.currency', ''));
    }
}

if (! function_exists('brand_logo_url')) {
    function brand_logo_url(): string
    {
        $logo = setting('app_logo');

        if ($logo) {
            return asset('storage/'.$logo);
        }

        return asset('dashboard/assets/images/gahez-logo.png');
    }
}

if (! function_exists('brand_color')) {
    function brand_color(string $shade = '600'): string
    {
        return match ($shade) {
            '50' => '#fef6e7',
            '100' => '#fde4b6',
            '200' => '#fcd792',
            '300' => '#fac461',
            '400' => '#f9b942',
            '500' => '#f8a713',
            '600' => '#faad28',
            '700' => '#b0770d',
            '800' => '#885c0a',
            '900' => '#684608',
            '950' => '#4a3306',
            default => '#faad28',
        };
    }
}
