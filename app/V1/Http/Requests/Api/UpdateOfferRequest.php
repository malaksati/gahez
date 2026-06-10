<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\OfferValidation;

class UpdateOfferRequest extends ApiFormRequest
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
        return OfferValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'offerable_type.in' => 'Offer must apply to a product or category.',
            'value.min' => 'Offer value must be at least 0.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ]);
    }
}
