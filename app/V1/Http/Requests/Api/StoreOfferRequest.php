<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\OfferValidation;

class StoreOfferRequest extends ApiFormRequest
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
        return OfferValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.en.required' => 'English offer name is required.',
            'start_date.required' => 'Offer start date is required.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ]);
    }
}
