<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\AddressValidation;
use App\V1\Http\Requests\Rules\PhoneValidation;

class StoreAddressRequest extends ApiFormRequest
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
        return AddressValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'address.required' => 'Please enter the street address.',
            'latitude.required' => 'Please provide a latitude.',
            'longitude.required' => 'Please provide a longitude.',
            'name.required' => 'Please enter a label for this address (e.g. Home).',
        ]);
    }

    protected function prepareForValidation(): void
    {
        PhoneValidation::prepareRequest($this, ['phone']);
    }
}
