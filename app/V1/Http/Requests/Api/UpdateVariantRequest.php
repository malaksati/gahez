<?php

namespace App\V1\Http\Requests\Api;

use App\V1\Http\Requests\Rules\VariantValidation;

class UpdateVariantRequest extends ApiFormRequest
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
        return VariantValidation::update();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->mergeMessages([
            'name.array' => 'Variant name must be provided as translations.',
            'name.en.max' => 'English variant name may not exceed 255 characters.',
            'name.ar.max' => 'Arabic variant name may not exceed 255 characters.',
        ]);
    }
}
