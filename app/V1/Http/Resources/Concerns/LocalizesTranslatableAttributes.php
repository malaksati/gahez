<?php

namespace App\V1\Http\Resources\Concerns;

use Illuminate\Http\Request;

trait LocalizesTranslatableAttributes
{
    /**
     * Active locale (set by SetLocaleFromRequest middleware from ?locale= or Accept-Language).
     */
    protected function locale(?Request $request = null): string
    {
        return app()->getLocale();
    }

    /**
     * Resolve a translatable attribute on the current resource model.
     */
    protected function localized(string $field, ?string $locale = null, ?Request $request = null): mixed
    {
        return $this->localizedValue($this->{$field}, $locale, $request);
    }

    /**
     * Resolve a raw translatable value (array, JSON string, or plain string).
     */
    protected function localizedValue(mixed $value, ?string $locale = null, ?Request $request = null): mixed
    {
        $locale ??= $this->locale($request);

        if (is_array($value)) {
            return $value[$locale] ?? $value['en'] ?? reset($value) ?: null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return $decoded[$locale] ?? $decoded['en'] ?? reset($decoded) ?: null;
            }
        }

        return $value;
    }
}
