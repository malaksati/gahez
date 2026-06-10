<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\CouponValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Validator;

class UpdateCouponRequest extends AdminFormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $coupon = $this->route('coupon');

        return CouponValidation::update(is_object($coupon) ? $coupon->getKey() : (int) $coupon);
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
        return (new \App\V1\Http\Requests\Api\UpdateCouponRequest)->messages();
    }
}
