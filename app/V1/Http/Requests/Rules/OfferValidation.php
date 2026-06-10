<?php

namespace App\V1\Http\Requests\Rules;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Validation\Rule;

final class OfferValidation
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return array_merge(TranslatableRules::field('name'), [
            'type' => ['required', Rule::in(['fixed', 'percentage', 'bogo', 'threshold_gift', 'free_delivery'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'bogo_buy_quantity' => ['nullable', 'integer', 'min:1', 'max:2'],
            'bogo_bonus_quantity' => ['nullable', 'integer', 'min:1', 'max:2'],
            'bogo_bonus_discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'bogo_bonus_discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discounted_quantity' => ['nullable', 'integer', 'min:1'],
            'ends_when_out_of_stock' => ['sometimes', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'show_countdown' => ['sometimes', 'boolean'],
            'offerable_type' => ['nullable', 'string', Rule::in([Product::class, Category::class])],
            'offerable_id' => ['nullable', 'integer'],
            'reward_product_ids' => ['nullable', 'array'],
            'reward_product_ids.*' => ['integer', 'exists:products,id'],
        ]);
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function update(): array
    {
        return array_merge(TranslatableRules::field('name', required: false), [
            'type' => ['sometimes', Rule::in(['fixed', 'percentage', 'bogo', 'threshold_gift', 'free_delivery'])],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'bogo_buy_quantity' => ['nullable', 'integer', 'min:1', 'max:2'],
            'bogo_bonus_quantity' => ['nullable', 'integer', 'min:1', 'max:2'],
            'bogo_bonus_discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'bogo_bonus_discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discounted_quantity' => ['nullable', 'integer', 'min:1'],
            'ends_when_out_of_stock' => ['sometimes', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
            'show_countdown' => ['sometimes', 'boolean'],
            'offerable_type' => ['nullable', 'string', Rule::in([Product::class, Category::class])],
            'offerable_id' => ['nullable', 'integer'],
            'reward_product_ids' => ['nullable', 'array'],
            'reward_product_ids.*' => ['integer', 'exists:products,id'],
        ]);
    }
}
