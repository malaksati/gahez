<?php

namespace App\V1\Http\Requests\Rules;

final class CartItemValidation
{
    /**
     * @return array<string, list<string>>
     */
    public static function add(): array
    {
        return [
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function updateByCartItem(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function updateQuantity(): array
    {
        return [
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function remove(): array
    {
        return [
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function store(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_unit_id' => ['nullable', 'integer', 'exists:product_units,id'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
