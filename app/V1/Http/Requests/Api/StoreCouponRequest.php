<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\CouponValidation;
use Illuminate\Validation\Validator;

class StoreCouponRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return CouponValidation::store();
    }

    public function withValidator(Validator $validator): void
    {
        CouponValidation::validateBusinessRules($validator);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'code.unique' => 'This coupon code is already taken.',
            'discount_value.min' => 'Discount value must be at least 0.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ]);
    }
}
