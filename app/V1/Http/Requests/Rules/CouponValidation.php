<?php

namespace App\V1\Http\Requests\Rules;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class CouponValidation
{
    /**
     * @return array<string, list<mixed>>
     */
    public static function store(): array
    {
        return self::rules();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public static function update(?int $couponId = null): array
    {
        return self::rules($couponId, partial: true);
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected static function rules(?int $couponId = null, bool $partial = false): array
    {
        $codeRule = $partial
            ? ['sometimes', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($couponId)]
            : ['required', 'string', 'max:50', Rule::unique('coupons', 'code')];

        $typeRule = $partial
            ? ['sometimes', Rule::in(['fixed', 'percentage', 'free_delivery'])]
            : ['required', Rule::in(['fixed', 'percentage', 'free_delivery'])];

        return [
            'code' => $codeRule,
            'type' => $typeRule,
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'first_order_only' => ['sometimes', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public static function validateBusinessRules(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $data = $validator->getData();
            $type = (string) ($data['type'] ?? '');

            if (! in_array($type, ['fixed', 'percentage'], true)) {
                return;
            }

            $value = $data['discount_value'] ?? null;

            if ($value === null || $value === '') {
                $validator->errors()->add('discount_value', __('messages.Discount value is required.'));
            }
        });
    }
}
