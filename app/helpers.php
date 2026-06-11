<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

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

if (! function_exists('display_currency')) {
    /**
     * Locale-aware currency label for UI (stored EGP → L.E. or ج.م).
     */
    function display_currency(?string $code = null): string
    {
        $code = strtoupper(trim($code ?? app_currency()));

        if ($code === '') {
            return '';
        }

        if ($code === 'EGP') {
            return uses_arabic_indic_digits() ? 'ج.م' : 'L.E.';
        }

        return $code;
    }
}

if (! function_exists('storage_public_url')) {
    /**
     * Public URL for a file stored on the "public" disk (relative path, e.g. settings/foo.png).
     */
    function storage_public_url(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('storage_public_path')) {
    /**
     * Absolute filesystem path for a public-disk file, or null when missing.
     */
    function storage_public_path(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->path($path);
    }
}

if (! function_exists('brand_logo_url')) {
    function brand_logo_url(): string
    {
        $url = storage_public_url(setting('app_logo'));

        if ($url !== null && storage_public_path(setting('app_logo')) !== null) {
            return $url;
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

if (! function_exists('uses_arabic_indic_digits')) {
    function uses_arabic_indic_digits(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return str_starts_with(strtolower($locale), 'ar');
    }
}

if (! function_exists('localize_digits')) {
    function localize_digits(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (! uses_arabic_indic_digits()) {
            return $value;
        }

        return strtr($value, [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
        ]);
    }
}

if (! function_exists('format_local_number')) {
    function format_local_number(
        float|int|string|null $number,
        int $decimals = 0,
        ?string $decimalSeparator = null,
        ?string $thousandsSeparator = null,
    ): string {
        if ($number === null || $number === '') {
            $number = 0;
        }

        if (uses_arabic_indic_digits()) {
            $decimalSeparator ??= '٫';
            $thousandsSeparator ??= '٬';
        } else {
            $decimalSeparator ??= '.';
            $thousandsSeparator ??= ',';
        }

        $formatted = number_format((float) $number, $decimals, $decimalSeparator, $thousandsSeparator);

        return uses_arabic_indic_digits()
            ? localize_digits($formatted)
            : $formatted;
    }
}

if (! function_exists('local_num')) {
    /**
     * Format a database numeric value for the active locale (Arabic → ٠–٩).
     */
    function local_num(
        float|int|string|null $number,
        int $decimals = 0,
        ?string $decimalSeparator = null,
        ?string $thousandsSeparator = null,
    ): string {
        return format_local_number($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}
