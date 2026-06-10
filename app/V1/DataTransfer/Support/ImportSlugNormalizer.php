<?php

namespace App\V1\DataTransfer\Support;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Support\Str;

/**
 * Normalizes slugs for imports to match {@see Sluggable} output (Str::slug).
 */
final class ImportSlugNormalizer
{
    /**
     * Resolve slug from spreadsheet value or generate from name (name.en source on models).
     */
    public static function fromNameSource(?string $slug, string $nameSource): string
    {
        $slug = trim((string) $slug);
        $nameSource = trim($nameSource);

        if ($slug === '') {
            return Str::slug($nameSource !== '' ? $nameSource : 'item');
        }

        return self::normalize($slug, $nameSource);
    }

    /**
     * Ensure slug is already in Str::slug form; otherwise re-slug the value or fall back to name.
     */
    public static function normalize(string $slug, ?string $fallbackNameSource = null): string
    {
        $slug = trim($slug);

        if ($slug === '') {
            return self::fromNameSource(null, (string) $fallbackNameSource);
        }

        $canonical = Str::slug($slug);

        if ($canonical !== '' && $canonical === $slug) {
            return $slug;
        }

        if ($canonical !== '') {
            return $canonical;
        }

        return self::fromNameSource(null, (string) $fallbackNameSource);
    }
}
