<?php

namespace App\V1\Http\Requests\Api;

use App\Models\Address;
use App\V1\Http\Requests\Rules\AddressValidation;
use App\V1\Http\Requests\Rules\PhoneValidation;

class UpdateAddressRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) {
            return false;
        }

        return Address::query()
            ->whereKey((int) $this->route('id'))
            ->where('user_id', $this->user()->id)
            ->exists();
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return AddressValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([]);
    }

    protected function prepareForValidation(): void
    {
        PhoneValidation::prepareRequest($this, ['phone']);
    }
}
