<?php

namespace App\V1\DataTransfer\Support;

/**
 * Normalizes spreadsheet boolean cells for category, product, and variant imports.
 *
 * Accepts: 0/1, true/false (any case), and common words (active, inactive, featured, etc.).
 */
final class ImportBooleanParser
{
    /** @var list<string> */
    private const FALSE_VALUES = [
        '0',
        '0.0',
        'false',
        'f',
        'no',
        'n',
        'off',
        'inactive',
        'in-active',
        'in active',
        'not active',
        'not-active',
        'notactive',
        'not featured',
        'not-featured',
        'notfeatured',
        'not approved',
        'not-approved',
        'notapproved',
        'unapproved',
        'pending',
        'not required',
        'not-required',
        'notrequired',
        'optional',
        'not new',
        'not-new',
        'notnew',
        'not bookable',
        'not-bookable',
        'notbookable',
        'disabled',
        '—',
        '-',
    ];

    /** @var list<string> */
    private const TRUE_VALUES = [
        '1',
        '1.0',
        'true',
        't',
        'yes',
        'y',
        'on',
        'active',
        'enabled',
        'featured',
        'approved',
        'required',
        'new',
        'bookable',
    ];

    public static function parse(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }

        $normalized = self::normalize((string) $value);

        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, self::FALSE_VALUES, true)) {
            return false;
        }

        if (in_array($normalized, self::TRUE_VALUES, true)) {
            return true;
        }

        if (str_starts_with($normalized, 'not ')) {
            return false;
        }

        if (is_numeric($normalized)) {
            return ((float) $normalized) != 0.0;
        }

        return $default;
    }

    private static function normalize(string $value): string
    {
        $value = strtolower(trim($value));

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }
}
