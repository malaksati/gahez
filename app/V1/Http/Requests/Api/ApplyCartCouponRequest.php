<?php

namespace App\V1\Http\Requests\Api;

class ApplyCartCouponRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
        ];
    }
}
