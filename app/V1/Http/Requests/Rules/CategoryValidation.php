<?php

namespace App\V1\Http\Requests\Rules;

use Illuminate\Validation\Rule;

final class CategoryValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return array_merge(TranslatableRules::field('name'), [
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return array_merge(TranslatableRules::field('name', required: false), [
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['sometimes', 'integer', 'min:1'],
        ]);
    }

    /**
     * Spreadsheet import (parent is linked in a second pass; do not validate parent_id here).
     *
     * @return array<string, list<mixed>>
     */
    public static function import(?int $existingCategoryId = null): array
    {
        return array_merge(TranslatableRules::field('name'), [
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($existingCategoryId),
            ],
            'image' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
