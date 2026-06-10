<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Concerns\MapsOfferableInput;
use App\V1\Http\Requests\Rules\OfferValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;
use Illuminate\Validation\Validator;

class UpdateOfferRequest extends AdminFormRequest
{
    use MapsOfferableInput;

    protected function prepareForValidation(): void
    {
        $this->prepareOfferableInput();
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return $this->withOfferableRules(OfferValidation::update(), required: false);
    }

    /**
     * @return array<string, string>
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateOfferBusinessRules($validator);
        });
    }

    public function messages(): array
    {
        return array_merge((new \App\V1\Http\Requests\Api\UpdateOfferRequest)->messages(), [
            'offerable_type_key.in' => __('messages.offerable_type_key.in'),
            'offerable_id.exists' => __('messages.offerable_id.exists'),
        ]);
    }
}
