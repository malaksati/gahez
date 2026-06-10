<?php

namespace App\V1\DataTransfer\Support;

use Illuminate\Support\Str;

final class SpreadsheetHeaderNormalizer
{
    public static function normalize(string $header): string
    {
        $header = trim($header);

        if ($header === '') {
            return '';
        }

        $header = preg_replace('/\s*\(\s*en\s*\)/iu', ' en', $header) ?? $header;
        $header = preg_replace('/\s*\(\s*ar\s*\)/iu', ' ar', $header) ?? $header;
        $header = str_replace(['(', ')'], '', $header);
        $header = preg_replace('/\bURLs\b/i', 'urls', $header) ?? $header;
        $header = preg_replace('/\bURL\b/i', 'url', $header) ?? $header;
        $header = preg_replace('/\bSKU\b/i', 'sku', $header) ?? $header;
        $header = preg_replace('/\bID\b/', 'id', $header) ?? $header;
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return Str::snake($header);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, list<string>>  $aliases
     * @return array<string, mixed>
     */
    public static function applyAliases(array $row, array $aliases): array
    {
        foreach ($aliases as $canonical => $alternateKeys) {
            if (self::hasValue($row[$canonical] ?? null)) {
                continue;
            }

            foreach ($alternateKeys as $key) {
                if (self::hasValue($row[$key] ?? null)) {
                    $row[$canonical] = $row[$key];
                    break;
                }
            }
        }

        return $row;
    }

    private static function hasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return trim((string) $value) !== '';
    }
}
