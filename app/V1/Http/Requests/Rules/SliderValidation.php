<?php

namespace App\V1\Http\Requests\Rules;

use App\Models\Slider;
use Illuminate\Validation\Rule;

final class SliderValidation
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return [
            'image' => ['required', 'image', 'max:4096'],
            'type' => ['required', 'string', Rule::in(Slider::types())],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function update(): array
    {
        return [
            'image' => ['sometimes', 'image', 'max:4096'],
            'type' => ['sometimes', 'string', Rule::in(Slider::types())],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function apiIndexFilter(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::in(Slider::types())],
        ];
    }
}
