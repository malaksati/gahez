<?php

namespace App\V1\Http\Requests\Rules;

final class TranslatableRules
{
    /**
     * @param  list<string>  $locales
     * @return array<string, list<string>>
     */
    public static function field(
        string $field,
        bool $required = true,
        int $max = 255,
        array $locales = ['en', 'ar'],
    ): array {
        $parentRule = $required ? 'required' : 'sometimes';

        $rules = [
            $field => [$parentRule, 'array'],
        ];

        foreach ($locales as $locale) {
            $rules["{$field}.{$locale}"] = [
                $required ? 'required' : 'sometimes',
                'string',
                "max:{$max}",
            ];
        }

        return $rules;
    }
}
