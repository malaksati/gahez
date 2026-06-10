<?php

namespace App\V1\Support;

class StoreTheme
{
    /**
     * @return list<string>
     */
    public static function layoutOptions(): array
    {
        return ['horizontal', 'vertical'];
    }

    /**
     * @return list<string>
     */
    public static function fontOptions(): array
    {
        return ['Cairo', 'Inter', 'Poppins', 'Roboto', 'Tajawal'];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'primary_color' => '#faad28',
            'secondary_color' => '#f8a713',
            'category_layout' => 'horizontal',
            'product_layout' => 'vertical',
            'font_family' => 'Cairo',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fromSettings(): array
    {
        $defaults = self::defaults();

        return [
            'primary_color' => (string) setting('store_primary_color', $defaults['primary_color']),
            'secondary_color' => (string) setting('store_secondary_color', $defaults['secondary_color']),
            'category_layout' => self::normalizeLayout((string) setting('store_category_layout', $defaults['category_layout'])),
            'product_layout' => self::normalizeLayout((string) setting('store_product_layout', $defaults['product_layout'])),
            'font_family' => self::normalizeFont((string) setting('store_font_family', $defaults['font_family'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public static function sanitizeInput(array $input): array
    {
        return [
            'primary_color' => self::normalizeColor($input['primary_color'] ?? self::defaults()['primary_color']),
            'secondary_color' => self::normalizeColor($input['secondary_color'] ?? self::defaults()['secondary_color']),
            'category_layout' => self::normalizeLayout($input['category_layout'] ?? self::defaults()['category_layout']),
            'product_layout' => self::normalizeLayout($input['product_layout'] ?? self::defaults()['product_layout']),
            'font_family' => self::normalizeFont($input['font_family'] ?? self::defaults()['font_family']),
        ];
    }

    public static function normalizeLayout(string $layout): string
    {
        return in_array($layout, self::layoutOptions(), true) ? $layout : self::defaults()['category_layout'];
    }

    public static function normalizeFont(string $font): string
    {
        return in_array($font, self::fontOptions(), true) ? $font : self::defaults()['font_family'];
    }

    public static function normalizeColor(string $color): string
    {
        $color = trim($color);

        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? strtoupper($color) : self::defaults()['primary_color'];
    }
}
