<?php

namespace App\V1\Http\Requests\Web\Admin;

use App\V1\Http\Requests\Rules\AddressValidation;
use App\V1\Http\Requests\Rules\PhoneValidation;
use App\V1\Http\Requests\Web\AdminFormRequest;

class UpdateAddressRequest extends AdminFormRequest
{
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
        return (new \App\V1\Http\Requests\Api\UpdateAddressRequest)->messages();
    }

    protected function prepareForValidation(): void
    {
        PhoneValidation::prepareRequest($this, ['phone']);
    }
}
