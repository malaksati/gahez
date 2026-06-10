<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\VariantValidation;

class StoreVariantRequest extends ApiFormRequest
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
        return VariantValidation::store();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.required' => 'Variant name translations are required.',
            'name.en.required' => 'English variant name is required.',
            'name.ar.required' => 'Arabic variant name is required.',
        ]);
    }
}
