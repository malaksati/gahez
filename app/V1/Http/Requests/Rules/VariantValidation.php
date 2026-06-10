<?php

namespace App\V1\Http\Requests\Rules;

use Illuminate\Validation\Rule;

final class VariantValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return array_merge(
            TranslatableRules::field('name'),
            self::flags(),
            self::options(),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    public static function update(): array
    {
        return array_merge(
            TranslatableRules::field('name', required: false),
            self::flags(),
            self::options(),
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private static function flags(): array
    {
        return [
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private static function options(): array
    {
        return [
            'options' => ['nullable', 'array'],
            'options.*.id' => ['sometimes', 'integer', 'exists:variant_options,id'],
            'options.*.name' => ['required', 'array'],
            'options.*.name.en' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.name.ar' => ['required_with:options.*.name', 'string', 'max:255'],
            'options.*.code' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Spreadsheet import for one variant row (with or without option columns).
     *
     * @param  array{variant: array<string, mixed>, option: ?array<string, mixed>}  $mapped
     * @return array<string, list<mixed>>
     */
    public static function importRow(array $mapped, ?int $existingVariantId = null): array
    {
        $rules = $existingVariantId
            ? self::update()
            : self::store();

        if ($mapped['option'] === null) {
            return array_diff_key($rules, array_flip([
                'options',
                'options.*.id',
                'options.*.name',
                'options.*.name.en',
                'options.*.name.ar',
                'options.*.code',
            ]));
        }

        $optionId = $mapped['option']['id'] ?? null;

        if ($optionId) {
            $rules['options.*.id'] = ['required', 'integer', Rule::exists('variant_options', 'id')];

            if ($existingVariantId) {
                $rules['options.*.id'][] = Rule::exists('variant_options', 'id')->where('variant_id', $existingVariantId);
            }

            $rules['options.*.code'] = ['nullable', 'string', 'max:50', Rule::unique('variant_options', 'code')->ignore($optionId)];
        } else {
            unset($rules['options.*.id']);
            $rules['options.*.code'] = ['nullable', 'string', 'max:50', Rule::unique('variant_options', 'code')];
        }

        return $rules;
    }
}
