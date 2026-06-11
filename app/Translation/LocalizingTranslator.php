<?php

namespace App\Translation;

use Illuminate\Translation\Translator;

class LocalizingTranslator extends Translator
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $line = parent::get($key, $replace, $locale, $fallback);

        if (! is_string($line)) {
            return $line;
        }

        $effectiveLocale = $locale ?? $this->locale;

        return uses_arabic_indic_digits($effectiveLocale)
            ? localize_digits($line)
            : $line;
    }
}
