<?php

namespace App\V1\DataTransfer\Support;

/**
 * Normalizes display names from spreadsheet imports (products, categories, variants).
 *
 * English names: first letter uppercase, remainder lowercase (e.g. "size" → "Size").
 * Arabic names: trimmed only (no case transformation).
 */
final class ImportDisplayNameNormalizer
{
    public static function name(string $value, string $locale = 'en'): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if ($locale === 'ar') {
            return $value;
        }

        $first = mb_substr($value, 0, 1);
        $rest = mb_substr($value, 1);

        return mb_strtoupper($first).mb_strtolower($rest);
    }
}
