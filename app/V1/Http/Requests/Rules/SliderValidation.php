<?php

namespace App\V1\Http\Requests\Rules;

final class SliderValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return [
            'image' => ['required', 'image', 'max:4096'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return [
            'image' => ['sometimes', 'image', 'max:4096'],
        ];
    }
}
