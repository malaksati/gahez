<?php

namespace App\V1\Http\Requests\Rules;

final class BranchValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return array_merge(TranslatableRules::field('name'), [
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'string', 'max:32'],
            'longitude' => ['required', 'string', 'max:32'],
            'phone' => PhoneValidation::rules(),
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return array_merge(TranslatableRules::field('name', required: false), [
            'address' => ['sometimes', 'string', 'max:500'],
            'latitude' => ['sometimes', 'string', 'max:32'],
            'longitude' => ['sometimes', 'string', 'max:32'],
            'phone' => PhoneValidation::rules(),
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
