<?php

namespace App\V1\Http\Requests\Rules;

use App\Models\Goal;
use Illuminate\Validation\Rule;

final class GoalValidation
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return array_merge(
            TranslatableRules::field('name'),
            TranslatableRules::field('description', required: false, max: 1000),
            self::baseFields(),
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function update(): array
    {
        return array_merge(
            TranslatableRules::field('name', required: false),
            TranslatableRules::field('description', required: false, max: 1000),
            self::baseFields(required: false),
        );
    }

    /**
     * @return array<string, list<mixed>>
     */
    private static function baseFields(bool $required = true): array
    {
        $sometimes = $required ? 'required' : 'sometimes';

        return [
            'period_type' => [$sometimes, 'string', Rule::in(Goal::periodTypes())],
            'min_order_total' => [$sometimes, 'numeric', 'min:0.01'],
            'reward_amount' => [$sometimes, 'numeric', 'min:0.01'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
