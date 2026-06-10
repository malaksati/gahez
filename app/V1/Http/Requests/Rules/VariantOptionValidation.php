<?php

namespace App\V1\Http\Requests\Rules;

final class VariantOptionValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return array_merge(TranslatableRules::field('name'), [
            'code' => ['required', 'string', 'max:50'],
            'variant_id' => ['required', 'integer', 'exists:variants,id'],
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return array_merge(TranslatableRules::field('name', required: false), [
            'code' => ['sometimes', 'string', 'max:50'],
            'variant_id' => ['sometimes', 'integer', 'exists:variants,id'],
        ]);
    }
}
