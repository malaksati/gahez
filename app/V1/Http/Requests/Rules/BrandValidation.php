<?php

namespace App\V1\Http\Requests\Rules;

final class BrandValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return TranslatableRules::field('name');
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return TranslatableRules::field('name', required: false);
    }
}
