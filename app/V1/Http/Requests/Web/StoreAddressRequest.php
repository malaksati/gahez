<?php

namespace App\V1\Http\Requests\Web;

use App\V1\Http\Requests\Rules\AddressValidation;

class StoreAddressRequest extends AdminFormRequest
{
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
        return (new \App\V1\Http\Requests\Api\StoreAddressRequest)->messages();
    }
}
